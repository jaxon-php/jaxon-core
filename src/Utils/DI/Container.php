<?php

/**
 * Container.php - Jaxon data container
 *
 * Provide container service for Jaxon utils class instances.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\DI;

use Jaxon\Jaxon;
use Jaxon\Response\Response;
use Jaxon\Request\Support\CallableRegistry;
use Jaxon\Request\Support\CallableRepository;
use Jaxon\Request\Plugin\CallableClass;
use Jaxon\Request\Plugin\CallableDir;
use Jaxon\Request\Plugin\CallableFunction;
use Jaxon\Request\Plugin\FileUpload;
use Jaxon\Request\Support\FileUpload as FileUploadSupport;
use Jaxon\Request\Handler\Handler as RequestHandler;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Factory\ParameterFactory;
use Jaxon\Request\Factory\CallableClass\Request as CallableClassRequestFactory;
use Jaxon\Request\Support\CallableObject;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Response\Plugin\JQuery as JQueryPlugin;
use Jaxon\Response\Plugin\DataBag;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Plugin\Code\Generator as CodeGenerator;
use Jaxon\Contracts\Session as SessionContract;

use Jaxon\App\App;
use Jaxon\App\Bootstrap;

use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\Reader as ConfigReader;
use Jaxon\Utils\View\Manager as ViewManager;
use Jaxon\Utils\View\Renderer as ViewRenderer;
use Jaxon\Utils\Dialogs\Dialog;
use Jaxon\Utils\Template\Minifier;
use Jaxon\Utils\Template\Engine as TemplateEngine;
use Jaxon\Utils\Template\View as TemplateView;
use Jaxon\Utils\Pagination\Paginator;
use Jaxon\Utils\Pagination\Renderer as PaginationRenderer;
use Jaxon\Utils\Validation\Validator;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Utils\Session\Manager as SessionManager;
use Jaxon\Utils\Http\URI;
use Jaxon\Utils\Config\Exception\Data as ConfigDataException;

use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;
use Lemon\Event\EventDispatcher;
use Closure;
use ReflectionClass;

use function realpath;

class Container extends PimpleContainer
{
    /**
     * The Dependency Injection Container
     *
     * @var ContainerInterface
     */
    private $appContainer = null;

    /**
     * The class constructor
     *
     * @param Jaxon $jaxon
     * @param array $aOptions The default options
     * @throws ConfigDataException
     */
    public function __construct(Jaxon $jaxon, array $aOptions)
    {
        parent::__construct();

        $this->val(Jaxon::class, $jaxon);

        $sTranslationDir = realpath(__DIR__ . '/../../../translations');
        $sTemplateDir = realpath(__DIR__ . '/../../../templates');
        // Translation directory
        $this->val('jaxon.core.translation_dir', $sTranslationDir);
        // Template directory
        $this->val('jaxon.core.template_dir', $sTemplateDir);

        $this->init();
        $this->getConfig()->setOptions($aOptions);
    }

    /**
     * Get the container provided by the integrated framework
     *
     * @return ContainerInterface
     */
    public function getAppContainer()
    {
        return $this->appContainer;
    }

    /**
     * Set the container provided by the integrated framework
     *
     * @param ContainerInterface  $container     The container implementation
     *
     * @return void
     */
    public function setAppContainer(ContainerInterface $container)
    {
        $this->appContainer = $container;
    }

    /**
     * Set the parameters and create the objects in the dependency injection container
     *
     * @return void
     */
    private function init()
    {
        /*
         * Core library objects
         */
        // Global Response
        $this->set(Response::class, function() {
            return new Response();
        });
        // Dialog
        $this->set(Dialog::class, function() {
            return new Dialog();
        });
        // Jaxon App
        $this->set(App::class, function($c) {
            return new App($c->g(Jaxon::class), $c->g(ResponseManager::class), $c->g(ConfigReader::class));
        });
        // Jaxon App bootstrap
        $this->set(Bootstrap::class, function($c) {
            return new Bootstrap($c->g(Jaxon::class), $c->g(PluginManager::class),
                $c->g(ViewManager::class), $c->g(RequestHandler::class));
        });

        /*
         * Plugins
         */
        // Callable objects repository
        $this->set(CallableRepository::class, function() {
            return new CallableRepository($this);
        });
        // Callable objects registry
        $this->set(CallableRegistry::class, function($c) {
            return new CallableRegistry($c->g(CallableRepository::class));
        });
        // Callable class plugin
        $this->set(CallableClass::class, function($c) {
            return new CallableClass($c->g(RequestHandler::class), $c->g(ResponseManager::class),
                $c->g(CallableRegistry::class), $c->g(CallableRepository::class));
        });
        // Callable dir plugin
        $this->set(CallableDir::class, function($c) {
            return new CallableDir($c->g(CallableRegistry::class));
        });
        // Callable function plugin
        $this->set(CallableFunction::class, function($c) {
            return new CallableFunction($this, $c->g(RequestHandler::class), $c->g(ResponseManager::class));
        });
        // File upload support
        $this->set(FileUploadSupport::class, function() {
            return new FileUploadSupport();
        });
        // File upload plugin
        $this->set(FileUpload::class, function($c) {
            return new FileUpload($c->g(ResponseManager::class), $c->g(FileUploadSupport::class));
        });
        // JQuery response plugin
        $this->set(JQueryPlugin::class, function() {
            return new JQueryPlugin();
        });
        // DataBag response plugin
        $this->set(DataBag::class, function() {
            return new DataBag();
        });

        /*
         * Managers
         */
        // Plugin Manager
        $this->set(PluginManager::class, function($c) {
            return new PluginManager($c->g(Jaxon::class), $c->g(CodeGenerator::class));
        });
        // Request Handler
        $this->set(RequestHandler::class, function($c) {
            return new RequestHandler($c->g(Jaxon::class), $c->g(PluginManager::class),
                $c->g(ResponseManager::class), $c->g(FileUpload::class), $c->g(DataBag::class));
        });
        // Request Factory
        $this->set(RequestFactory::class, function($c) {
            return new RequestFactory($c->g(CallableRegistry::class));
        });
        // Parameter Factory
        $this->set(ParameterFactory::class, function() {
            return new ParameterFactory();
        });
        // Response Manager
        $this->set(ResponseManager::class, function($c) {
            return new ResponseManager($c->g(Jaxon::class));
        });
        // Code Generator
        $this->set(CodeGenerator::class, function($c) {
            return new CodeGenerator($c->g(Jaxon::class), $c->g(URI::class), $c->g(TemplateEngine::class));
        });
        // View Manager
        $this->set(ViewManager::class, function() {
            $xViewManager = new ViewManager($this);
            // Add the default view renderer
            $xViewManager->addRenderer('jaxon', function($di) {
                return new TemplateView($di->get(TemplateEngine::class));
            });
            // By default, render pagination templates with Jaxon.
            $xViewManager->addNamespace('pagination', '', '.php', 'jaxon');
            return $xViewManager;
        });
        // View Renderer
        $this->set(ViewRenderer::class, function($c) {
            return new ViewRenderer($c->g(ViewManager::class));
        });
        // Set the default session manager
        $this->set(SessionContract::class, function() {
            return new SessionManager();
        });

        /*
         * Config
         */
        $this->set(Config::class, function() {
            return new Config();
        });
        $this->set(ConfigReader::class, function($c) {
            return new ConfigReader($c->g(Config::class));
        });

        /*
         * Services
         */
        // Minifier
        $this->set(Minifier::class, function() {
            return new Minifier();
        });
        // Translator
        $this->set(Translator::class, function($c) {
            return new Translator($c->g('jaxon.core.translation_dir'), $c->g(Config::class));
        });
        // Template engine
        $this->set(TemplateEngine::class, function($c) {
            return new TemplateEngine($c->g('jaxon.core.template_dir'));
        });
        // Validator
        $this->set(Validator::class, function($c) {
            return new Validator($c->g(Translator::class), $c->g(Config::class));
        });
        // Pagination Paginator
        $this->set(Paginator::class, function($c) {
            return new Paginator($c->g(PaginationRenderer::class));
        });
        // Pagination Renderer
        $this->set(PaginationRenderer::class, function($c) {
            return new PaginationRenderer($c->g(ViewRenderer::class));
        });
        // Event Dispatcher
        $this->set(EventDispatcher::class, function() {
            return new EventDispatcher();
        });
        // URI decoder
        $this->set(URI::class, function() {
            return new URI();
        });
    }

    /**
     * Check if a class is defined in the container
     *
     * @param string                $sClass             The full class name
     *
     * @return bool
     */
    public function has($sClass)
    {
        if($this->appContainer != null && $this->appContainer->has($sClass))
        {
            return true;
        }
        return $this->offsetExists($sClass);
    }

    /**
     * Get a class instance
     *
     * @param string                $sClass             The full class name
     *
     * @return mixed
     */
    public function g($sClass)
    {
        return $this->offsetGet($sClass);
    }

    /**
     * Get a class instance
     *
     * @param string                $sClass             The full class name
     *
     * @return mixed
     */
    public function get($sClass)
    {
        if($this->appContainer != null && $this->appContainer->has($sClass))
        {
            return $this->appContainer->get($sClass);
        }
        return $this->offsetGet($sClass);
    }

    /**
     * Save a closure in the container
     *
     * @param string                $sClass             The full class name
     * @param Closure               $xClosure           The closure
     *
     * @return void
     */
    public function set($sClass, Closure $xClosure)
    {
        $this->offsetSet($sClass, $xClosure);
    }

    /**
     * Save a value in the container
     *
     * @param string                $sKey               The key
     * @param mixed                 $xValue             The value
     *
     * @return void
     */
    public function val($sKey, $xValue)
    {
        $this->offsetSet($sKey, $xValue);
    }

    /**
     * Set an alias in the container
     *
     * @param string                $sAlias             The alias name
     * @param string                $sClass             The class name
     *
     * @return void
     */
    public function alias($sAlias, $sClass)
    {
        $this->set($sAlias, function($c) use ($sClass) {
            return $c->get($sClass);
        });
    }

    /**
     * Create an instance of a class, getting the contructor parameters from the DI container
     *
     * @param string|ReflectionClass    $xClass         The class name or the reflection class
     *
     * @return mixed
     */
    public function make($xClass)
    {
        if(is_string($xClass))
        {
            // Create the reflection class instance
            $xClass = new ReflectionClass($xClass);
        }
        if(!($xClass instanceof ReflectionClass))
        {
            return null;
        }
        // Use the Reflection class to get the parameters of the constructor
        if(($constructor = $xClass->getConstructor()) === null)
        {
            return $xClass->newInstance();
        }
        $parameters = $constructor->getParameters();
        $parameterInstances = [];
        foreach($parameters as $parameter)
        {
            // Get the parameter instance from the DI
            $parameterInstances[] = $this->get($parameter->getClass()->getName());
        }
        return $xClass->newInstanceArgs($parameterInstances);
    }

    /**
     * Create an instance of a class by automatically fetching the dependencies from the constructor.
     *
     * @param string                $sClass             The class name
     *
     * @return void
     */
    public function auto($sClass)
    {
        $this->set($sClass, function($c) use ($sClass) {
            return $this->make($sClass);
        });
    }

    /**
     * Get the plugin manager
     *
     * @return PluginManager
     */
    public function getPluginManager()
    {
        return $this->get(PluginManager::class);
    }

    /**
     * Get the request handler
     *
     * @return RequestHandler
     */
    public function getRequestHandler()
    {
        return $this->get(RequestHandler::class);
    }

    /**
     * Get the request factory
     *
     * @return RequestFactory
     */
    public function getRequestFactory()
    {
        return $this->get(RequestFactory::class);
    }

    /**
     * Get the parameter factory
     *
     * @return ParameterFactory
     */
    public function getParameterFactory()
    {
        return $this->get(ParameterFactory::class);
    }

    /**
     * Get the response manager
     *
     * @return ResponseManager
     */
    public function getResponseManager()
    {
        return $this->get(ResponseManager::class);
    }

    /**
     * Get the code generator
     *
     * @return CodeGenerator
     */
    public function getCodeGenerator()
    {
        return $this->get(CodeGenerator::class);
    }

    /**
     * Get the callable registry
     *
     * @return CallableRegistry
     */
    public function getCallableRegistry()
    {
        return $this->get(CallableRegistry::class);
    }

    /**
     * Get the config manager
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->get(Config::class);
    }

    /**
     * Get the config reader
     *
     * @return ConfigReader
     */
    public function getConfigReader()
    {
        return $this->get(ConfigReader::class);
    }

    /**
     * Create a new the config manager
     *
     * @param array             $aOptions           The options array
     * @param string            $sKeys              The keys of the options in the array
     *
     * @return Config            The config manager
     */
    public function newConfig(array $aOptions = [], $sKeys = '')
    {
        return new Config($aOptions, $sKeys);
    }

    /**
     * Get the dialog wrapper
     *
     * @return Dialog
     */
    public function getDialog()
    {
        return $this->get(Dialog::class);
    }

    /**
     * Get the minifier
     *
     * @return Minifier
     */
    public function getMinifier()
    {
        return $this->get(Minifier::class);
    }

    /**
     * Get the translator
     *
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->get(Translator::class);
    }

    /**
     * Get the template engine
     *
     * @return TemplateEngine
     */
    public function getTemplateEngine()
    {
        return $this->get(TemplateEngine::class);
    }

    /**
     * Get the validator
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->get(Validator::class);
    }

    /**
     * Get the paginator
     *
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->get(Paginator::class);
    }

    /**
     * Get the event dispatcher
     *
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->get(EventDispatcher::class);
    }

    /**
     * Get the global Response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->get(Response::class);
    }

    /**
     * Create a new Jaxon response object
     *
     * @return Response
     */
    public function newResponse()
    {
        return new Response();
    }

    /**
     * Get the App instance
     *
     * @return App
     */
    public function getApp()
    {
        return $this->get(App::class);
    }

    /**
     * Get the App bootstrap
     *
     * @return Bootstrap
     */
    public function getBootstrap()
    {
        return $this->get(Bootstrap::class);
    }

    /**
     * Get the view manager
     *
     * @return ViewManager
     */
    public function getViewManager()
    {
        return $this->get(ViewManager::class);
    }

    /**
     * Get the view facade
     *
     * @return ViewRenderer
     */
    public function getViewRenderer()
    {
        return $this->get(ViewRenderer::class);
    }

    /**
     * Get the session manager
     *
     * @return SessionContract
     */
    public function getSessionManager()
    {
        return $this->get(SessionContract::class);
    }

    /**
     * Set the session manager
     *
     * @param Closure      $xClosure      A closure to create the session manager instance
     *
     * @return void
     */
    public function setSessionManager(Closure $xClosure)
    {
        $this->set(SessionContract::class, $xClosure);
    }

    /**
     * Create a new callable object
     *
     * @param string        $sFunctionName      The function name
     * @param string        $sCallableFunction  The callable function name
     * @param array         $aOptions           The function options
     *
     * @return void
     */
    public function registerCallableFunction($sFunctionName, $sCallableFunction, array $aOptions)
    {
        $this->set($sFunctionName, function() use($sFunctionName, $sCallableFunction, $aOptions) {
            $xCallableFunction = new \Jaxon\Request\Support\CallableFunction($sCallableFunction);
            foreach($aOptions as $sName => $sValue)
            {
                $xCallableFunction->configure($sName, $sValue);
            }
            return $xCallableFunction;
        });
    }

    /**
     * @param mixed $xCallableObject
     * @param array $aOptions
     *
     * @return void
     */
    private function setCallableObjectOptions($xCallableObject, array $aOptions)
    {
        foreach(['namespace', 'separator', 'protected'] as $sName)
        {
            if(isset($aOptions[$sName]))
            {
                $xCallableObject->configure($sName, $aOptions[$sName]);
            }
        }

        if(!isset($aOptions['functions']))
        {
            return;
        }
        // Functions options
        $aCallableOptions = [];
        foreach($aOptions['functions'] as $sFunctionNames => $aFunctionOptions)
        {
            $aNames = explode(',', $sFunctionNames); // Names are in comma-separated list.
            foreach($aNames as $sFunctionName)
            {
                foreach($aFunctionOptions as $sOptionName => $xOptionValue)
                {
                    if(substr($sOptionName, 0, 2) !== '__')
                    {
                        // Options for javascript code.
                        $aCallableOptions[$sFunctionName][$sOptionName] = $xOptionValue;
                        continue;
                    }
                    // Options for PHP classes. They start with "__".
                    $xCallableObject->configure($sOptionName, [$sFunctionName => $xOptionValue]);
                }
            }
        }
        $xCallableObject->setOptions($aCallableOptions);
    }

    /**
     * Create a new callable object
     *
     * @param string        $sClassName         The callable class name
     * @param array         $aOptions           The callable object options
     *
     * @return void
     */
    public function registerCallableObject($sClassName, array $aOptions)
    {
        $sFactoryName = $sClassName . '_RequestFactory';
        $sCallableName = $sClassName . '_CallableObject';
        $sReflectionName = $sClassName . '_ReflectionClass';

        // Register the reflection class
        $this->set($sReflectionName, function() use($sClassName) {
            return new ReflectionClass($sClassName);
        });

        // Register the callable object
        $this->set($sCallableName, function($c) use($sReflectionName, $aOptions) {
            $xCallableObject = new CallableObject($this, $c->g($sReflectionName));
            $this->setCallableObjectOptions($xCallableObject, $aOptions);
            return $xCallableObject;
        });

        // Register the request factory
        $this->set($sFactoryName, function($c) use($sCallableName) {
            return new CallableClassRequestFactory($c->g($sCallableName));
        });

        // Register the user class
        $this->set($sClassName, function($c) use($sFactoryName, $sReflectionName) {
            $xRegisteredObject = $this->make($c->g($sReflectionName));
            // Initialize the object
            if($xRegisteredObject instanceof \Jaxon\CallableClass)
            {
                $xResponse = $this->getResponse();
                // Set the members of the object
                $cSetter = function() use($c, $xResponse, $sFactoryName) {
                    $this->jaxon = $c->g(Jaxon::class);
                    $this->sRequest = $sFactoryName;
                    $this->response = $xResponse;
                };
                $cSetter = $cSetter->bindTo($xRegisteredObject, $xRegisteredObject);
                // Can now access protected attributes
                call_user_func($cSetter);
            }

            // Run the callback for class initialisation
            $aCallbacks = $this->getRequestHandler()->getCallbackManager()->getInitCallbacks();
            foreach($aCallbacks as $xCallback)
            {
                call_user_func($xCallback, $xRegisteredObject);
            }
            return $xRegisteredObject;
        });
    }

    /**
     * Get a package instance
     *
     * @param string $sClassName The package class name
     * @param array $aAppOptions The package options defined in the app section of the config file
     *
     * @return Config
     */
    public function registerPackage($sClassName, array $aAppOptions)
    {
        $xAppConfig = $this->newConfig($aAppOptions);
        $this->set($sClassName, function() use($sClassName, $aAppOptions, $xAppConfig) {
            $xPackage = $this->make($sClassName);
            // Set the package options
            $cSetter = function($aOptions, $xConfig) {
                $this->aOptions = $aOptions;
                $this->xConfig = $xConfig;
            };
            // Can now access protected attributes
            call_user_func($cSetter->bindTo($xPackage, $xPackage), $aAppOptions, $xAppConfig);
            return $xPackage;
        });

        return $xAppConfig;
    }
}
