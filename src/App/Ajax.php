<?php

/**
 * Ajax.php
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

namespace Jaxon\App;

use Jaxon\Jaxon;
use Jaxon\Di\Container;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\Library\DialogLibraryManager;
use Jaxon\App\I18n\Translator;
use Jaxon\App\Session\SessionInterface;
use Jaxon\App\Traits\AjaxSendTrait;
use Jaxon\App\Traits\AjaxTrait;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Request\Call\Paginator;
use Jaxon\Request\Factory\Psr\PsrFactory;
use Jaxon\Request\Upload\UploadHandlerInterface;
use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Response\Response;
use Jaxon\Response\ResponseInterface;
use Jaxon\Utils\Template\TemplateEngine;

use function trim;

class Ajax
{
    use AjaxTrait;
    use AjaxSendTrait;

    /**
     * @var Ajax
     */
    private static $xInstance = null;

    /**
     * @var Bootstrap
     */
    protected $xBootstrap;

    /**
     * @param Container $xContainer
     *
     * @return void
     */
    private function init(Container $xContainer)
    {
        $this->xContainer = $xContainer;
        // Set the attributes from the container
        $this->xBootstrap = $xContainer->g(Bootstrap::class);
        $this->xTranslator = $xContainer->g(Translator::class);
        $this->xConfigManager = $xContainer->g(ConfigManager::class);
        $this->xPluginManager = $xContainer->g(PluginManager::class);
        $this->xResponseManager = $xContainer->g(ResponseManager::class);
    }

    /**
     * @return Ajax
     */
    public static function getInstance(): Ajax
    {
        if(self::$xInstance === null)
        {
            // First call: create and initialize the instances.
            self::$xInstance = new Ajax();
            self::$xInstance->init(new Container(self::$xInstance));
            return self::$xInstance;
        }

        // Call the on boot callbacks on each call to the jaxon() function, except the first.
        self::$xInstance->xBootstrap->onBoot();
        return self::$xInstance;
    }

    /**
     * The constructor
     */
    private function __construct()
    {}

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return Jaxon::VERSION;
    }

    /**
     * Read the options from the file, if provided, and return the config
     *
     * @param string $sConfigFile The full path to the config file
     * @param string $sConfigSection The section of the config file to be loaded
     *
     * @return ConfigManager
     * @throws SetupException
     */
    public function config(string $sConfigFile = '', string $sConfigSection = ''): ConfigManager
    {
        if(!empty(($sConfigFile = trim($sConfigFile))))
        {
            $this->xConfigManager->load($sConfigFile, trim($sConfigSection));
        }
        return $this->xConfigManager;
    }

    /**
     * Get the global Response object
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->xResponseManager->getResponse();
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
     * @param array $xPkgOptions    The user provided package options
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, array $xPkgOptions = [])
    {
        $this->di()->getPackageManager()->registerPackage($sClassName, $xPkgOptions);
    }

    /**
     * @return UploadHandlerInterface|null
     */
    public function upload(): ?UploadHandlerInterface
    {
        return $this->di()->getUploadHandler();
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
     * @see <AjaxTrait::canProcessRequest>
     */
    public function processRequest()
    {
        // Process the jaxon request
        $this->di()->getRequestHandler()->processRequest();

        $this->sendResponse();
    }

    /**
     * @return PsrFactory
     */
    public function psr(): PsrFactory
    {
        return $this->di()->getPsrFactory();
    }

    /**
     * @return AppInterface
     */
    public function app(): AppInterface
    {
        return $this->di()->getApp();
    }

    /**
     * @return TemplateEngine
     */
    public function template(): TemplateEngine
    {
        return $this->di()->getTemplateEngine();
    }

    /**
     * @return ViewRenderer
     */
    public function view(): ViewRenderer
    {
        return $this->di()->getViewRenderer();
    }

    /**
     * @return Paginator
     */
    public function paginator(): Paginator
    {
        return $this->di()->getPaginator();
    }

    /**
     * @return DialogLibraryManager
     */
    public function dialog(): DialogLibraryManager
    {
        return $this->di()->getDialogLibraryManager();
    }

    /**
     * @return SessionInterface|null
     */
    public function session(): ?SessionInterface
    {
        return $this->di()->getSessionManager();
    }

    /**
     * @return void
     * @throws SetupException
     */
    public function reset()
    {
        self::$xInstance = null;
        // Need to register the default plugins.
        self::getInstance()->di()->getPluginManager()->registerPlugins();
    }
}
