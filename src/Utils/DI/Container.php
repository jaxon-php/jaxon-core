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

use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;
use Lemon\Event\EventDispatcher;
use Closure;
use ReflectionClass;

class Container
{
    /**
     * The Dependency Injection Container
     *
     * @var PimpleContainer
     */
    private $libContainer = null;

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
     * @throws \Jaxon\Utils\Config\Exception\Data
     */
    public function __construct(Jaxon $jaxon, array $aOptions)
    {
        $this->libContainer = new PimpleContainer();
        $this->libContainer[Jaxon::class] = $jaxon;

        $sTranslationDir = realpath(__DIR__ . '/../../../translations');
        $sTemplateDir = realpath(__DIR__ . '/../../../templates');
        $this->init($sTranslationDir, $sTemplateDir);
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
     * @param string        $sTranslationDir     The translation directory
     * @param string        $sTemplateDir        The template directory
     *
     * @return void
     */
    private function init($sTranslationDir, $sTemplateDir)
    {
        /*
         * Parameters
         */
        // Translation directory
        $this->libContainer['jaxon.core.translation_dir'] = $sTranslationDir;
        // Template directory
        $this->libContainer['jaxon.core.template_dir'] = $sTemplateDir;

        /*
         * Core library objects
         */
        // Global Response
        $this->libContainer[Response::class] = function() {
            return new Response();
        };
        // Dialog
        $this->libContainer[Dialog::class] = function() {
            return new Dialog();
        };
        // Jaxon App
        $this->libContainer[App::class] = function($c) {
            return new App($c[Jaxon::class], $c[ResponseManager::class], $c[ConfigReader::class]);
        };
        // Jaxon App bootstrap
        $this->libContainer[Bootstrap::class] = function($c) {
            return new Bootstrap($c[Jaxon::class], $c[PluginManager::class],
                $c[ViewManager::class], $c[RequestHandler::class]);
        };

        /*
         * Plugins
         */
        // Callable objects repository
        $this->libContainer[CallableRepository::class] = function() {
            return new CallableRepository($this);
        };
        // Callable objects registry
        $this->libContainer[CallableRegistry::class] = function($c) {
            return new CallableRegistry($c[CallableRepository::class]);
        };
        // Callable class plugin
        $this->libContainer[CallableClass::class] = function($c) {
            return new CallableClass($c[RequestHandler::class], $c[ResponseManager::class],
                $c[CallableRegistry::class], $c[CallableRepository::class]);
        };
        // Callable dir plugin
        $this->libContainer[CallableDir::class] = function($c) {
            return new CallableDir($c[CallableRegistry::class]);
        };
        // Callable function plugin
        $this->libContainer[CallableFunction::class] = function($c) {
            return new CallableFunction($this, $c[RequestHandler::class], $c[ResponseManager::class]);
        };
        // File upload support
        $this->libContainer[FileUploadSupport::class] = function() {
            return new FileUploadSupport();
        };
        // File upload plugin
        $this->libContainer[FileUpload::class] = function($c) {
            return new FileUpload($c[ResponseManager::class], $c[FileUploadSupport::class]);
        };
        // JQuery response plugin
        $this->libContainer[JQueryPlugin::class] = function() {
            return new JQueryPlugin();
        };
        // DataBag response plugin
        $this->libContainer[DataBag::class] = function() {
            return new DataBag();
        };

        /*
         * Managers
         */
        // Plugin Manager
        $this->libContainer[PluginManager::class] = function($c) {
            return new PluginManager($c[Jaxon::class], $c[CodeGenerator::class]);
        };
        // Request Handler
        $this->libContainer[RequestHandler::class] = function($c) {
            return new RequestHandler($c[Jaxon::class], $c[PluginManager::class],
                $c[ResponseManager::class], $c[FileUpload::class], $c[DataBag::class]);
        };
        // Request Factory
        $this->libContainer[RequestFactory::class] = function($c) {
            return new RequestFactory($c[CallableRegistry::class]);
        };
        // Parameter Factory
        $this->libContainer[ParameterFactory::class] = function() {
            return new ParameterFactory();
        };
        // Response Manager
        $this->libContainer[ResponseManager::class] = function($c) {
            return new ResponseManager($c[Jaxon::class]);
        };
        // Code Generator
        $this->libContainer[CodeGenerator::class] = function($c) {
            return new CodeGenerator($c[Jaxon::class], $c[URI::class], $c[TemplateEngine::class]);
        };
        // View Manager
        $this->libContainer[ViewManager::class] = function() {
            $xViewManager = new ViewManager($this);
            // Add the default view renderer
            $xViewManager->addRenderer('jaxon', function($di) {
                return new TemplateView($di->get(TemplateEngine::class));
            });
            // By default, render pagination templates with Jaxon.
            $xViewManager->addNamespace('pagination', '', '.php', 'jaxon');
            return $xViewManager;
        };
        // View Renderer
        $this->libContainer[ViewRenderer::class] = function($c) {
            return new ViewRenderer($c[ViewManager::class]);
        };
        // Set the default session manager
        $this->libContainer[SessionContract::class] = function() {
            return new SessionManager();
        };

        /*
         * Config
         */
        $this->libContainer[Config::class] = function() {
            return new Config();
        };
        $this->libContainer[ConfigReader::class] = function($c) {
            return new ConfigReader($c[Config::class]);
        };

        /*
         * Services
         */
        // Minifier
        $this->libContainer[Minifier::class] = function() {
            return new Minifier();
        };
        // Translator
        $this->libContainer[Translator::class] = function($c) {
            return new Translator($c['jaxon.core.translation_dir'], $c[Config::class]);
        };
        // Template engine
        $this->libContainer[TemplateEngine::class] = function($c) {
            return new TemplateEngine($c['jaxon.core.template_dir']);
        };
        // Validator
        $this->libContainer[Validator::class] = function($c) {
            return new Validator($c[Translator::class], $c[Config::class]);
        };
        // Pagination Paginator
        $this->libContainer[Paginator::class] = function($c) {
            return new Paginator($c[PaginationRenderer::class]);
        };
        // Pagination Renderer
        $this->libContainer[PaginationRenderer::class] = function($c) {
            return new PaginationRenderer($c[ViewRenderer::class]);
        };
        // Event Dispatcher
        $this->libContainer[EventDispatcher::class] = function() {
            return new EventDispatcher();
        };
        // URI decoder
        $this->libContainer[URI::class] = function() {
            return new URI();
        };
    }

    /**
     * Get a class instance
     *
     * @return object        The class instance
     */
    public function get($sClass)
    {
        if($this->appContainer != null && $this->appContainer->has($sClass))
        {
            return $this->appContainer->get($sClass);
        }
        return $this->libContainer[$sClass];
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
        $this->libContainer[$sClass] = function() use($xClosure) {
            return call_user_func($xClosure, $this);
        };
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
        $this->libContainer[$sKey] = $xValue;
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
        $this->libContainer[$sAlias] = function($c) use ($sClass) {
            return $c[$sClass];
        };
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
        if(($constructor = $xClass->getConstructor()) == null)
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
        $this->libContainer[$sClass] = function($c) use ($sClass) {
            return $this->make($sClass);
        };
    }

    /**
     * Get the plugin manager
     *
     * @return PluginManager
     */
    public function getPluginManager()
    {
        return $this->libContainer[PluginManager::class];
    }

    /**
     * Get the request handler
     *
     * @return RequestHandler
     */
    public function getRequestHandler()
    {
        return $this->libContainer[RequestHandler::class];
    }

    /**
     * Get the request factory
     *
     * @return RequestFactory
     */
    public function getRequestFactory()
    {
        return $this->libContainer[RequestFactory::class];
    }

    /**
     * Get the parameter factory
     *
     * @return ParameterFactory
     */
    public function getParameterFactory()
    {
        return $this->libContainer[ParameterFactory::class];
    }

    /**
     * Get the response manager
     *
     * @return ResponseManager
     */
    public function getResponseManager()
    {
        return $this->libContainer[ResponseManager::class];
    }

    /**
     * Get the code generator
     *
     * @return CodeGenerator
     */
    public function getCodeGenerator()
    {
        return $this->libContainer[CodeGenerator::class];
    }

    /**
     * Get the callable registry
     *
     * @return CallableRegistry
     */
    public function getCallableRegistry()
    {
        return $this->libContainer[CallableRegistry::class];
    }

    /**
     * Get the config manager
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->libContainer[Config::class];
    }

    /**
     * Get the config reader
     *
     * @return ConfigReader
     */
    public function getConfigReader()
    {
        return $this->libContainer[ConfigReader::class];
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
        return $this->libContainer[Dialog::class];
    }

    /**
     * Get the minifier
     *
     * @return Minifier
     */
    public function getMinifier()
    {
        return $this->libContainer[Minifier::class];
    }

    /**
     * Get the translator
     *
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->libContainer[Translator::class];
    }

    /**
     * Get the template engine
     *
     * @return TemplateEngine
     */
    public function getTemplateEngine()
    {
        return $this->libContainer[TemplateEngine::class];
    }

    /**
     * Get the validator
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->libContainer[Validator::class];
    }

    /**
     * Get the paginator
     *
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->libContainer[Paginator::class];
    }

    /**
     * Get the event dispatcher
     *
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->libContainer[EventDispatcher::class];
    }

    /**
     * Get the global Response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->libContainer[Response::class];
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
        return $this->libContainer[App::class];
    }

    /**
     * Get the App bootstrap
     *
     * @return Bootstrap
     */
    public function getBootstrap()
    {
        return $this->libContainer[Bootstrap::class];
    }

    /**
     * Get the view manager
     *
     * @return ViewManager
     */
    public function getViewManager()
    {
        return $this->libContainer[ViewManager::class];
    }

    /**
     * Get the view facade
     *
     * @return ViewRenderer
     */
    public function getViewRenderer()
    {
        return $this->libContainer[ViewRenderer::class];
    }

    /**
     * Get the session manager
     *
     * @return SessionContract
     */
    public function getSessionManager()
    {
        return $this->libContainer[SessionContract::class];
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
        $this->libContainer[SessionContract::class] = $xClosure;
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
            return [];
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
     * @return CallableObject
     */
    public function registerCallableObject($sClassName, array $aOptions)
    {
        $sFactoryName = $sClassName . '_RequestFactory';
        $sCallableName = $sClassName . '_CallableObject';
        $sReflectionName = $sClassName . '_ReflectionClass';

        // Register the reflection class
        $this->libContainer[$sReflectionName] = function($c) use($sClassName) {
            return new ReflectionClass($sClassName);
        };

        // Register the callable object
        $this->libContainer[$sCallableName] = function($c) use($sReflectionName, $aOptions) {
            $xCallableObject = new CallableObject($this, $c[$sReflectionName]);
            $this->setCallableObjectOptions($xCallableObject, $aOptions);
            return $xCallableObject;
        };

        // Register the request factory
        $this->libContainer[$sFactoryName] = function($c) use($sCallableName) {
            return new CallableClassRequestFactory($c[$sCallableName]);
        };

        // Register the user class
        $this->libContainer[$sClassName] = function($c) use($sFactoryName, $sReflectionName) {
            $xRegisteredObject = $this->make($c[$sReflectionName]);
            // Initialize the object
            if($xRegisteredObject instanceof \Jaxon\CallableClass)
            {
                $xResponse = $this->getResponse();
                // Set the members of the object
                $cSetter = function() use($c, $xResponse, $sFactoryName) {
                    $this->jaxon = $c[Jaxon::class];
                    $this->sRequest = $sFactoryName;
                    $this->response = $xResponse;
                };
                $cSetter = $cSetter->bindTo($xRegisteredObject, $xRegisteredObject);
                // Can now access protected attributes
                \call_user_func($cSetter);
            }

            // Run the callback for class initialisation
            $aCallbacks = $this->getRequestHandler()->getCallbackManager()->getInitCallbacks();
            foreach($aCallbacks as $xCallback)
            {
                \call_user_func($xCallback, $xRegisteredObject);
            }
            return $xRegisteredObject;
        };
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
            \call_user_func($cSetter->bindTo($xPackage, $xPackage), $aAppOptions, $xAppConfig);
            return $xPackage;
        });

        return $xAppConfig;
    }
}
