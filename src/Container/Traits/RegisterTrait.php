<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\CallableClass;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Plugin\CallableClass\CallableObject;
use Jaxon\Request\Plugin\CallableFunction\CallableFunction;
use Jaxon\Ui\Dialogs\Dialog;
use Jaxon\Ui\Pagination\Paginator;
use Jaxon\Ui\View\Renderer;
use Jaxon\Utils\Config\Config;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Translation\Translator;

use ReflectionClass;
use ReflectionException;

use function call_user_func;
use function explode;
use function substr;

trait RegisterTrait
{
    /**
     * Create a new callable object
     *
     * @param string $sJsFunction    The name of the js function
     * @param string $sFunctionName    The callable function name
     * @param array $aOptions    The function options
     *
     * @return void
     */
    public function registerCallableFunction(string $sJsFunction, string $sFunctionName, array $aOptions)
    {
        $this->set($sJsFunction, function() use($sJsFunction, $sFunctionName, $aOptions) {
            $xCallableFunction = new CallableFunction($sFunctionName);
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
            $xConfig = $c->g(Config::class);
            $xCallable = $c->g($sCallableObject);
            $sJsClass = $xConfig->getOption('core.prefix.class') . $xCallable->getJsName() . '.';
            return new RequestFactory($sJsClass, $c->g(Dialog::class), $c->g(Paginator::class));
        });

        // Register the user class
        $this->set($sClassName, function($c) use($sClassName, $sReflectionClass) {
            $xRegisteredObject = $this->make($c->g($sReflectionClass));
            // Initialize the object
            if($xRegisteredObject instanceof CallableClass)
            {
                $xResponse = $this->getResponse();
                // Set the members of the object
                $cSetter = function() use($c, $xResponse, $sClassName) {
                    $this->jaxon = $c->g(Jaxon::class);
                    $this->response = $xResponse;
                    $this->_class = $sClassName;
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
     * Get a package instance
     *
     * @param string $sClassName    The package class name
     * @param array $aAppOptions    The package options defined in the app section of the config file
     *
     * @return Config
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, array $aAppOptions): Config
    {
        $xAppConfig = $this->newConfig($aAppOptions);
        $this->val($sClassName . '_config', $xAppConfig);
        $this->set($sClassName, function($c) use($sClassName) {
            $xPackage = $this->make($sClassName);
            // Set the package options
            $cSetter = function($c, $sClassName) {
                $this->xPkgConfig = $c->g($sClassName . '_config');
                $this->xRenderer = $c->g(Renderer::class);
            };
            // Can now access protected attributes
            call_user_func($cSetter->bindTo($xPackage, $xPackage), $c, $sClassName);
            return $xPackage;
        });

        return $xAppConfig;
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
}
