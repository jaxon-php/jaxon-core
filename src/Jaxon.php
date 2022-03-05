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
use Jaxon\Plugin\Package;
use Jaxon\Plugin\Response as ResponsePlugin;
use Jaxon\Request\Factory\CallableClass\Request;
use Jaxon\Request\Handler\Callback;
use Jaxon\Request\Plugin\FileUpload;
use Jaxon\Response\Response;
use Jaxon\Ui\Dialogs\Dialog;
use Jaxon\Ui\View\Renderer;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\Reader as ConfigReader;
use Jaxon\Utils\Template\Engine;
use Jaxon\Utils\Translation\Translator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Jaxon implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Package version number
     *
     * @const string
     */
    const VERSION = 'Jaxon 3.8.0';

    /*
     * Plugin types
     */
    // Response plugin
    const PLUGIN_RESPONSE = 'ResponsePlugin';
    // Request plugin
    const PLUGIN_REQUEST = 'RequestPlugin';
    // Package plugin
    const PLUGIN_PACKAGE = 'PackagePlugin';

    /*
     * Request plugins
     */
    const CALLABLE_CLASS = 'CallableClass';
    const CALLABLE_DIR = 'CallableDir';
    const CALLABLE_FUNCTION = 'CallableFunction';
    // For uploaded files.
    const FILE_UPLOAD = 'FileUpload';

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
     * Get the static instance
     *
     * @return Jaxon
     * @throws Exception\SetupException
     */
    public static function getInstance(): ?Jaxon
    {
        if(self::$xInstance === null)
        {
            self::$xContainer = new Container(self::getDefaultOptions());
            self::$xInstance = new Jaxon(self::$xContainer->g(Config::class),
                self::$xContainer->g(Translator::class));
            // Save the Jaxon instance in the DI
            self::$xContainer->val(Jaxon::class, self::$xInstance);
            /*
             * Register the Jaxon request and response plugins
             */
            self::$xContainer->getPluginManager()->registerRequestPlugins();
            self::$xContainer->getPluginManager()->registerResponsePlugins();
        }
        return self::$xInstance;
    }

    /**
     * The constructor
     */
    private function __construct(Config $xConfig, Translator $xTranslator)
    {
        $this->xConfig = $xConfig;
        $this->xTranslator = $xTranslator;
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
        return $this->di()->getConfig();
    }

    /**
     * Get the config reader
     *
     * @return ConfigReader
     */
    public function getConfigReader(): ConfigReader
    {
        return $this->di()->getConfigReader();
    }

    /**
     * Get the configured character encoding
     *
     * @return string
     */
    public function getCharacterEncoding(): string
    {
        return trim($this->config()->getOption('core.encoding'));
    }

    /**
     * Get the Global Response object
     *
     * @return Response
     */
    public function getResponse(): Response
    {
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
     * - 0 thru 999: Plugins that are part of or extensions to the jaxon core
     * - 1000 thru 8999: User created plugins, typically, these plugins don't care about order
     * - 9000 thru 9999: Plugins that generally need to be last or near the end of the plugin list
     *
     * @param string $sClassName The plugin class
     * @param string $sPluginName The plugin name
     * @param integer $nPriority The plugin priority, used to order the plugins
     *
     * @return void
     * @throws Exception\SetupException
     */
    public function registerPlugin(string $sClassName, string $sPluginName, int $nPriority = 1000)
    {
        $this->di()->getPluginManager()->registerPlugin($sClassName, $sPluginName, $nPriority);
    }

    /**
     * Register request handlers, including functions, callable classes and directories.
     *
     * @param string $sType The type of request handler being registered
     *        Options include:
     *        - Jaxon::CALLABLE_FUNCTION: a function declared at global scope
     *        - Jaxon::CALLABLE_CLASS: a class who's methods are to be registered
     *        - Jaxon::CALLABLE_DIR: a directory containing classes to be registered
     *        - Jaxon::PACKAGE: a package
     * @param string $sName
     *        When registering a function, this is the name of the function
     *        When registering a callable class, this is the class name
     *        When registering a callable directory, this is the full path to the directory
     *        When registering a package or a plugin, this is the corresponding class name
     * @param array|string $xOptions The related options
     *
     * @return void
     * @throws Exception\SetupException
     */
    public function register(string $sType, string $sName, $xOptions = [])
    {
        if($sType == self::CALLABLE_DIR ||
            $sType == self::CALLABLE_CLASS ||
            $sType == self::CALLABLE_FUNCTION)
        {
            $this->di()->getPluginManager()->registerCallable($sType, $sName, $xOptions);
            return;
        }
        /*
        if($sType == self::PLUGIN_RESPONSE)
        {
            $this->di()->getPluginManager()->registerRequestPlugin($sName, $xOptions);
            return;
        }
        if($sType == self::PLUGIN_REQUEST)
        {
            $this->di()->getPluginManager()->registerResponsePlugin($sName, $xOptions);
            return;
        }
        */
        if($sType == self::PLUGIN_PACKAGE && is_array($xOptions))
        {
            $this->di()->getPluginManager()->registerPackage($sName, $xOptions);
            return;
        }
        // Todo: throw an error
    }

    /**
     * Get an instance of a registered class
     *
     * @param string        $sClassName         The class name
     *
     * @return null|object
     */
    public function instance(string $sClassName)
    {
        $xCallable = $this->di()->getCallableRegistry()->getCallableObject($sClassName);
        return ($xCallable) ? $xCallable->getRegisteredObject() : null;
    }

    /**
     * Get a request to a registered class
     *
     * @param string        $sClassName         The class name
     *
     * @return Request|null
     */
    public function request(string $sClassName): ?Request
    {
        $xInstance = $this->instance($sClassName);
        return ($xInstance) ? $xInstance->rq() : null;
    }

    /**
     * Returns the Jaxon Javascript header and wrapper code to be printed into the page
     *
     * The javascript code returned by this function is dependent on the plugins
     * that are included and the functions and classes that are registered.
     *
     * @param bool $bIncludeJs Also get the JS files
     * @param bool $bIncludeCss Also get the CSS files
     *
     * @return string
     * @throws Utils\Http\UriException
     */
    public function getScript(bool $bIncludeJs = false, bool $bIncludeCss = false): string
    {
        return $this->di()->getCodeGenerator()->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Print the jaxon Javascript header and wrapper code into your page
     *
     * The javascript code returned by this function is dependent on the plugins
     * that are included and the functions and classes that are registered.
     *
     * @param bool $bIncludeJs Also print the JS files
     * @param bool $bIncludeCss Also print the CSS files
     *
     * @return void
     * @throws Utils\Http\UriException
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
        return $this->di()->getCodeGenerator()->getJs();
    }

    /**
     * Return the CSS header code and file includes
     *
     * @return string
     */
    public function getCss(): string
    {
        return $this->di()->getCodeGenerator()->getCss();
    }

    /**
     * Determine if a call is a jaxon request or a page load request
     *
     * @return bool
     */
    public function canProcessRequest(): bool
    {
        return $this->di()->getRequestHandler()->canProcessRequest();
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
     * @throws Exception\SetupException
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

        $this->di()->getRequestHandler()->processRequest();

        if(($this->xConfig->getOption('core.response.send')))
        {
            $this->di()->getResponseManager()->sendOutput();
            if(($this->xConfig->getOption('core.process.exit')))
            {
                exit();
            }
        }
    }

    /**
     * Get a registered response plugin
     *
     * @param string        $sName                The name of the plugin
     *
     * @return ResponsePlugin
     */
    public function plugin(string $sName): ResponsePlugin
    {
        return $this->di()->getPluginManager()->getResponsePlugin($sName);
    }

    /**
     * Get a package instance
     *
     * @param string        $sClassName           The package class name
     *
     * @return Package
     */
    public function package(string $sClassName): Package
    {
        return $this->di()->getPluginManager()->getPackage($sClassName);
    }

    /**
     * Get the upload plugin
     *
     * @return FileUpload
     */
    public function upload(): FileUpload
    {
        return $this->di()->getPluginManager()->getRequestPlugin(self::FILE_UPLOAD);
    }

    /**
     * Get the request callback manager
     *
     * @return Callback
     */
    public function callback(): Callback
    {
        return $this->di()->getRequestHandler()->getCallbackManager();
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
     * @return Engine
     */
    public function template(): Engine
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
