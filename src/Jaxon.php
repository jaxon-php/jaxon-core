<?php

/**
 * Jaxon.php - Jaxon class
 *
 * The Jaxon class uses a modular plug-in system to facilitate the processing
 * of special Ajax requests made by a PHP page.
 * It generates Javascript that the page must include in order to make requests.
 * It handles the output of response commands (see <Jaxon\Response\Response>).
 * Many flags and settings can be adjusted to effect the behavior of the Jaxon class
 * as well as the client-side javascript.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon;

use Jaxon\App\App;
use Jaxon\Container\Container;
use Jaxon\Contracts\Session;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Code\Generator as CodeGenerator;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Plugin\Package;
use Jaxon\Plugin\Response as ResponsePlugin;
use Jaxon\Request\Factory\Factory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\Callback;
use Jaxon\Request\Handler\Handler as RequestHandler;
use Jaxon\Request\Support\CallableRegistry;
use Jaxon\Request\Upload\Plugin as UploadPlugin;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Response\AbstractResponse;
use Jaxon\Response\Response;
use Jaxon\Ui\Dialogs\Dialog;
use Jaxon\Ui\View\Renderer;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\Exception\DataDepth;
use Jaxon\Utils\Config\Exception\FileAccess;
use Jaxon\Utils\Config\Exception\FileContent;
use Jaxon\Utils\Config\Exception\FileExtension;
use Jaxon\Utils\Config\Exception\YamlExtension;
use Jaxon\Utils\Config\Reader as ConfigReader;
use Jaxon\Utils\Template\Engine as TemplateEngine;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Utils\Http\UriException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function headers_sent;

class Jaxon implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Package version number
     *
     * @const string
     */
    const VERSION = 'Jaxon 4.0.0-dev';

    /*
     * Request plugins
     */
    const CALLABLE_CLASS = 'CallableClass';
    const CALLABLE_DIR = 'CallableDir';
    const CALLABLE_FUNCTION = 'CallableFunction';

    /**
     * A static instance on this class
     *
     * @var Jaxon
     */
    private static $xInstance = null;

    /**
     * The DI container
     *
     * @var Container
     */
    private static $xContainer = null;

    /**
     * @var Config
     */
    protected $xConfig;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * @var ConfigReader
     */
    protected $xConfigReader;

    /**
     * @var PluginManager
     */
    protected $xPluginManager;

    /**
     * @var CodeGenerator
     */
    protected $xCodeGenerator;

    /**
     * @var CallableRegistry
     */
    protected $xCallableRegistry;

    /**
     * @var RequestHandler
     */
    protected $xRequestHandler;

    /**
     * @var ResponseManager
     */
    protected $xResponseManager;

    /**
     * @return void
     */
    private static function initInstance()
    {
        // Save the Jaxon instance in the DI
        self::$xContainer->val(Jaxon::class, self::$xInstance);
        // Set the attributes from the container
        self::$xInstance->xConfig = self::$xContainer->g(Config::class);
        self::$xInstance->xTranslator = self::$xContainer->g(Translator::class);
        self::$xInstance->xConfigReader = self::$xContainer->g(ConfigReader::class);
        self::$xInstance->xPluginManager = self::$xContainer->g(PluginManager::class);
        self::$xInstance->xCodeGenerator = self::$xContainer->g(CodeGenerator::class);
        self::$xInstance->xCallableRegistry = self::$xContainer->g(CallableRegistry::class);
        self::$xInstance->xRequestHandler = self::$xContainer->g(RequestHandler::class);
        self::$xInstance->xResponseManager = self::$xContainer->g(ResponseManager::class);
    }

    /**
     * Get the static instance
     *
     * @return Jaxon
     */
    public static function getInstance(): ?Jaxon
    {
        if(self::$xInstance === null)
        {
            self::$xContainer = new Container(self::getDefaultOptions());
            self::$xInstance = new Jaxon();
            self::initInstance();
        }
        return self::$xInstance;
    }

    /**
     * The constructor
     */
    private function __construct()
    {
        // Set the default logger
        $this->setLogger(new NullLogger());
    }

    /**
     * The current Jaxon version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * Get the default options of all components of the library
     *
     * @return array<string,string|bool|integer>
     */
    private static function getDefaultOptions(): array
    {
        // The default configuration settings.
        return [
            'core.version'                      => self::VERSION,
            'core.language'                     => 'en',
            'core.encoding'                     => 'utf-8',
            'core.decode_utf8'                  => false,
            'core.prefix.function'              => 'jaxon_',
            'core.prefix.class'                 => 'Jaxon',
            // 'core.request.uri'               => '',
            'core.request.mode'                 => 'asynchronous',
            'core.request.method'               => 'POST', // W3C: Method is case sensitive
            'core.response.send'                => true,
            'core.response.merge.ap'            => true,
            'core.response.merge.js'            => true,
            'core.debug.on'                     => false,
            'core.debug.verbose'                => false,
            'core.process.exit'                 => true,
            'core.process.clean'                => false,
            'core.process.timeout'              => 6000,
            'core.error.handle'                 => false,
            'core.error.log_file'               => '',
            'core.jquery.no_conflict'           => false,
            'core.upload.enabled'               => true,
            'js.lib.output_id'                  => 0,
            'js.lib.queue_size'                 => 0,
            'js.lib.load_timeout'               => 2000,
            'js.lib.show_status'                => false,
            'js.lib.show_cursor'                => true,
            'js.app.dir'                        => '',
            'js.app.minify'                     => true,
            'js.app.options'                    => '',
        ];
    }

    /**
     * Get the DI container
     *
     * @return Container
     */
    public function di(): ?Container
    {
        return self::$xContainer;
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Get the library config
     *
     * @return Config
     */
    public function config(): Config
    {
        return $this->xConfig;
    }

    /**
     * Read a config file
     *
     * @param string $sConfigFile
     *
     * @return array
     * @throws SetupException
     */
    public function readConfig(string $sConfigFile): array
    {
        try
        {
            return $this->xConfigReader->read($sConfigFile);
        }
        catch(YamlExtension $e)
        {
            $sMessage = $this->xTranslator->trans('errors.yaml.install');
            throw new SetupException($sMessage);
        }
        catch(FileExtension $e)
        {
            $sMessage = $this->xTranslator->trans('errors.file.extension', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
        catch(FileAccess $e)
        {
            $sMessage = $this->xTranslator->trans('errors.file.access', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
        catch(FileContent $e)
        {
            $sMessage = $this->xTranslator->trans('errors.file.content', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Load a config file
     *
     * @param string $sConfigFile
     * @param string $sConfigSection
     *
     * @return void
     * @throws SetupException
     */
    public function loadConfig(string $sConfigFile, string $sConfigSection = '')
    {
        $aConfigOptions = $this->readConfig($sConfigFile);
        try
        {
            // Set up the lib config options.
            $this->xConfig->setOptions($aConfigOptions, $sConfigSection);
        }
        catch(DataDepth $e)
        {
            $sMessage = $this->xTranslator->trans('errors.data.depth', ['key' => $e->sPrefix, 'depth' => $e->nDepth]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Set the value of a config option
     *
     * @param string $sName    The option name
     * @param mixed $sValue    The option value
     *
     * @return void
     */
    public function setOption(string $sName, $sValue)
    {
        $this->xConfig->setOption($sName, $sValue);
    }

    /**
     * Get the value of a config option
     *
     * @param string $sName    The option name
     * @param mixed|null $xDefault    The default value, to be returned if the option is not defined
     *
     * @return mixed        The option value, or null if the option is unknown
     */
    public function getOption(string $sName, $xDefault = null)
    {
        return $this->xConfig->getOption($sName, $xDefault);
    }

    /**
     * Check the presence of a config option
     *
     * @param string $sName    The option name
     *
     * @return bool        True if the option exists, and false if not
     */
    public function hasOption(string $sName): bool
    {
        return $this->xConfig->hasOption($sName);
    }

    /**
     * Create a new the config set
     *
     * @return Config            The config manager
     * @throws SetupException
     */
    public function newConfig(): Config
    {
        return $this->di()->newConfig();
    }

    /**
     * Get the configured character encoding
     *
     * @return string
     */
    public function getCharacterEncoding(): string
    {
        return trim($this->xConfig->getOption('core.encoding'));
    }

    /**
     * Get a translated string
     *
     * @param string $sText    The key of the translated string
     * @param array $aPlaceHolders    The placeholders of the translated string
     * @param string $sLanguage    The language of the translated string
     *
     * @return string
     */
    public function trans(string $sText, array $aPlaceHolders = [], string $sLanguage = ''): string
    {
        return $this->xTranslator->trans($sText, $aPlaceHolders, $sLanguage);
    }

    /**
     * Get the Global Response object
     *
     * @return AbstractResponse
     */
    public function getResponse(): AbstractResponse
    {
        if(($xResponse = $this->xResponseManager->getResponse()))
        {
            return $xResponse;
        }
        return $this->di()->getResponse();
    }

    /**
     * Create a new Jaxon response object
     *
     * @return Response
     */
    public function newResponse(): Response
    {
        return $this->di()->newResponse();
    }

    /**
     * Register a plugin
     *
     * Below is a table for priorities and their description:
     * - 0 to 999: Plugins that are part of or extensions to the jaxon core
     * - 1000 to 8999: User created plugins, typically, these plugins don't care about order
     * - 9000 to 9999: Plugins that generally need to be last or near the end of the plugin list
     *
     * @param string $sClassName    The plugin class
     * @param string $sPluginName    The plugin name
     * @param integer $nPriority    The plugin priority, used to order the plugins
     *
     * @return void
     * @throws SetupException
     */
    public function registerPlugin(string $sClassName, string $sPluginName, int $nPriority = 1000)
    {
        $this->xPluginManager->registerPlugin($sClassName, $sPluginName, $nPriority);
    }

    /**
     * Register a package
     *
     * @param string $sClassName    The package class
     * @param array $aOptions    The package options
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, array $aOptions = [])
    {
        $this->xPluginManager->registerPackage($sClassName, $aOptions);
    }

    /**
     * Register request handlers, including functions, callable classes and directories.
     *
     * @param string $sType    The type of request handler being registered
     *        Options include:
     *        - Jaxon::CALLABLE_FUNCTION: a function declared at global scope
     *        - Jaxon::CALLABLE_CLASS: a class who's methods are to be registered
     *        - Jaxon::CALLABLE_DIR: a directory containing classes to be registered
     * @param string $sName
     *        When registering a function, this is the name of the function
     *        When registering a callable class, this is the class name
     *        When registering a callable directory, this is the full path to the directory
     * @param array|string $xOptions    The related options
     *
     * @return void
     * @throws SetupException
     */
    public function register(string $sType, string $sName, $xOptions = [])
    {
        $this->xPluginManager->registerCallable($sType, $sName, $xOptions);
    }

    /**
     * Get an instance of a registered class
     *
     * @param string $sClassName    The class name
     *
     * @return null|object
     */
    public function instance(string $sClassName)
    {
        $xCallable = $this->xCallableRegistry->getCallableObject($sClassName);
        return ($xCallable) ? $xCallable->getRegisteredObject() : null;
    }

    /**
     * Get the factory
     *
     * @return Factory
     */
    public function factory(): Factory
    {
        return $this->di()->g(Factory::class);
    }

    /**
     * Get a request to a registered class
     *
     * @param string $sClassName    The class name
     *
     * @return RequestFactory|null
     */
    public function request(string $sClassName): ?RequestFactory
    {
        return $this->factory()->request($sClassName);
    }

    /**
     * Returns the Jaxon Javascript header and wrapper code to be printed into the page
     *
     * The javascript code returned by this function is dependent on the plugins
     * that are included and the functions and classes that are registered.
     *
     * @param bool $bIncludeJs    Also get the JS files
     * @param bool $bIncludeCss    Also get the CSS files
     *
     * @return string
     * @throws UriException
     */
    public function getScript(bool $bIncludeJs = false, bool $bIncludeCss = false): string
    {
        return $this->xCodeGenerator->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Print the jaxon Javascript header and wrapper code into your page
     *
     * The javascript code returned by this function is dependent on the plugins
     * that are included and the functions and classes that are registered.
     *
     * @param bool $bIncludeJs    Also print the JS files
     * @param bool $bIncludeCss    Also print the CSS files
     *
     * @return void
     * @throws UriException
     */
    public function printScript(bool $bIncludeJs = false, bool $bIncludeCss = false)
    {
        print $this->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Return the javascript header code and file includes
     *
     * @return string
     */
    public function getJs(): string
    {
        return $this->xCodeGenerator->getJs();
    }

    /**
     * Return the CSS header code and file includes
     *
     * @return string
     */
    public function getCss(): string
    {
        return $this->xCodeGenerator->getCss();
    }

    /**
     * Determine if a call is a jaxon request or a page load request
     *
     * @return bool
     */
    public function canProcessRequest(): bool
    {
        return $this->xRequestHandler->canProcessRequest();
    }

    /**
     * If this is a jaxon request, call the requested PHP function, build the response and send it back to the browser
     *
     * This is the main server side engine for Jaxon.
     * It handles all the incoming requests, including the firing of events and handling of the response.
     * If your RequestURI is the same as your web page, then this function should be called before ANY
     * headers or HTML is output from your script.
     *
     * This function may exit after the request is processed, if the 'core.process.exit' option is set to true.
     *
     * @return void
     *
     * @throws RequestException
     * @see <Jaxon\Jaxon->canProcessRequest>
     */
    public function processRequest()
    {
        // Check to see if headers have already been sent out, in which case we can't do our job
        if(headers_sent($filename, $linenumber))
        {
            echo $this->xTranslator->trans('errors.output.already-sent', [
                'location' => $filename . ':' . $linenumber
            ]), "\n", $this->xTranslator->trans('errors.output.advice');
            exit();
        }

        $this->xRequestHandler->processRequest();

        if(($this->xConfig->getOption('core.response.send')))
        {
            $this->xResponseManager->sendOutput();
            if(($this->xConfig->getOption('core.process.exit')))
            {
                exit();
            }
        }
    }

    /**
     * Get a registered response plugin
     *
     * @param string $sName    The name of the plugin
     *
     * @return ResponsePlugin
     */
    public function plugin(string $sName): ResponsePlugin
    {
        return $this->xPluginManager->getResponsePlugin($sName);
    }

    /**
     * Get a package instance
     *
     * @param string $sClassName    The package class name
     *
     * @return Package
     */
    public function package(string $sClassName): Package
    {
        return $this->xPluginManager->getPackage($sClassName);
    }

    /**
     * Get the upload plugin
     *
     * @return UploadPlugin|null
     */
    public function upload(): ?UploadPlugin
    {
        return $this->di()->getUploadPlugin();
    }

    /**
     * Get the request callback manager
     *
     * @return Callback
     */
    public function callback(): Callback
    {
        return $this->xRequestHandler->getCallbackManager();
    }

    /**
     * Get the dialog wrapper
     *
     * @return Dialog
     */
    public function dialog(): Dialog
    {
        return $this->di()->getDialog();
    }

    /**
     * Get the template engine
     *
     * @return TemplateEngine
     */
    public function template(): TemplateEngine
    {
        return $this->di()->getTemplateEngine();
    }

    /**
     * Get the App instance
     *
     * @return App
     */
    public function app(): App
    {
        return $this->di()->getApp();
    }

    /**
     * Get the view renderer
     *
     * @return Renderer
     */
    public function view(): Renderer
    {
        return $this->di()->getViewRenderer();
    }

    /**
     * Get the session manager
     *
     * @return Session
     */
    public function session(): Session
    {
        return $this->di()->getSessionManager();
    }
}
