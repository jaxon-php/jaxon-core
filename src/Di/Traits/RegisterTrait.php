<?php

namespace Jaxon\Di\Traits;

use Jaxon\CallableClass;
use Jaxon\Config\ConfigManager;
use Jaxon\Exception\SetupException;
use Jaxon\Jaxon;
use Jaxon\Plugin\Request\CallableClass\CallableObject;
use Jaxon\Request\Factory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Ui\Dialog\Library\DialogLibraryHelper;
use Jaxon\Ui\Dialog\Library\DialogLibraryManager;
use Jaxon\Ui\Pagination\Paginator;
use Jaxon\Ui\View\ViewRenderer;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Template\TemplateEngine;
use Jaxon\Utils\Translation\Translator;
use ReflectionClass;
use ReflectionException;
use function call_user_func;
use function explode;
use function substr;

trait RegisterTrait
{
    /**
     * @param mixed $xCallableObject
     * @param array $aOptions
     *
     * @return void
     */
    private function setCallableObjectOptions($xCallableObject, array $aOptions)
    {
        foreach(['separator', 'protected'] as $sName)
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
        foreach($aOptions['functions'] as $sNames => $aFunctionOptions)
        {
            if($sNames === 'protected')
            {
                $xCallableObject->configure('protected', $aFunctionOptions);
                continue;
            }
            $aNames = explode(',', $sNames); // Names are in comma-separated list.
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
     * Register a callable class
     *
     * @param string $sClassName The callable class name
     * @param array $aOptions The callable object options
     *
     * @return void
     * @throws SetupException
     */
    public function registerCallableClass(string $sClassName, array $aOptions)
    {
        $sRequestFactory = $sClassName . '_RequestFactory';
        $sCallableObject = $sClassName . '_CallableObject';
        $sReflectionClass = $sClassName . '_ReflectionClass';

        // Make sure the registered class exists
        if(isset($aOptions['include']))
        {
            require_once($aOptions['include']);
        }
        // Register the reflection class
        try
        {
            $this->val($sReflectionClass, new ReflectionClass($sClassName));
        }
        catch(ReflectionException $e)
        {
            $xTranslator = $this->g(Translator::class);
            $sMessage = $xTranslator->trans('errors.class.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }

        // Register the callable object
        $this->set($sCallableObject, function($c) use($sReflectionClass, $aOptions) {
            $xCallableObject = new CallableObject($this, $c->g($sReflectionClass));
            $this->setCallableObjectOptions($xCallableObject, $aOptions);
            return $xCallableObject;
        });

        // Register the request factory
        $this->set($sRequestFactory, function($c) use($sCallableObject) {
            $xConfigManager = $c->g(ConfigManager::class);
            $xCallable = $c->g($sCallableObject);
            $sJsClass = $xConfigManager->getOption('core.prefix.class') . $xCallable->getJsName() . '.';
            return new RequestFactory($sJsClass, $c->g(DialogLibraryManager::class), $c->g(Paginator::class));
        });

        // Register the user class
        $this->set($sClassName, function($c) use($sClassName, $sReflectionClass) {
            $xRegisteredObject = $this->make($c->g($sReflectionClass));
            // Initialize the object
            if($xRegisteredObject instanceof CallableClass)
            {
                // Set the protected attributes of the object
                $cSetter = function($c, $sClassName) {
                    $this->jaxon = $c->g(Jaxon::class);
                    $this->response = $c->getResponse();
                    $this->_class = $sClassName;
                };
                // Can now access protected attributes
                call_user_func($cSetter->bindTo($xRegisteredObject, $xRegisteredObject), $c, $sClassName);
            }

            // Run the callback for class initialisation
            $aCallbacks = $c->g(CallbackManager::class)->getInitCallbacks();
            foreach($aCallbacks as $xCallback)
            {
                call_user_func($xCallback, $xRegisteredObject);
            }
            return $xRegisteredObject;
        });
    }

    /**
     * Get the callable object for a given class
     *
     * @param string $sClassName
     *
     * @return CallableObject
     */
    public function getCallableObject(string $sClassName): CallableObject
    {
        return $this->g($sClassName . '_CallableObject');
    }

    /**
     * Get the request factory for a given class
     *
     * @param string $sClassName
     *
     * @return RequestFactory
     */
    public function getRequestFactory(string $sClassName): RequestFactory
    {
        return $this->g($sClassName . '_RequestFactory');
    }

    /**
     * Register a package
     *
     * @param string $sClassName    The package class name
     * @param Config $xPkgConfig    The user provided package options
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, Config $xPkgConfig)
    {
        $this->val($sClassName . '_config', $xPkgConfig);
        $this->set($sClassName, function($c) use($sClassName) {
            $xPackage = $this->make($sClassName);
            // Set the protected attributes of the object
            $cSetter = function($c, $sClassName) {
                $this->xPkgConfig = $c->g($sClassName . '_config');
                $this->xFactory = $c->g(Factory::class);
                $this->xRenderer = $c->g(ViewRenderer::class);
            };
            // Can now access protected attributes
            call_user_func($cSetter->bindTo($xPackage, $xPackage), $c, $sClassName);
            return $xPackage;
        });
    }

    /**
     * Get a package config
     *
     * @param string $sClassName    The package class name
     *
     * @return Config
     */
    public function getPackageConfig(string $sClassName): Config
    {
        return $this->g($sClassName . '_config');
    }

    /**
     * Register a javascript dialog library adapter.
     *
     * @param string $sClass
     * @param string $sName
     *
     * @return void
     */
    public function registerDialogLibrary(string $sClass, string $sName)
    {
        $this->set($sName, function($c) use($sClass) {
            // Set the protected attributes of the library
            $cSetter = function() use($c) {
                $this->xHelper = new DialogLibraryHelper($this, $c->g(ConfigManager::class), $c->g(TemplateEngine::class));
            };
            // Can now access protected attributes
            $xLibrary = $c->make($sClass);
            call_user_func($cSetter->bindTo($xLibrary, $xLibrary));
            return $xLibrary;
        });
    }
}