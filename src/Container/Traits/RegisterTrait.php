<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Support\CallableObject;
use Jaxon\Request\Support\CallableFunction;
use Jaxon\Ui\Pagination\Paginator;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\Exception\DataDepth;

use ReflectionClass;

use function substr;
use function explode;
use function call_user_func;

trait RegisterTrait
{
    /**
     * Create a new callable object
     *
     * @param string        $sJsFunction     The name of the js function
     * @param string        $sFunctionName      The callable function name
     * @param array         $aOptions           The function options
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
     * Create a new callable object
     *
     * @param string        $sClassName         The callable class name
     * @param array         $aOptions           The callable object options
     *
     * @return void
     */
    public function registerCallableObject(string $sClassName, array $aOptions)
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
            $xConfig = $c->g(Config::class);
            $xCallable = $c->g($sCallableName);
            $sJsClass = $xConfig->getOption('core.prefix.class') . $xCallable->getJsName() . '.';
            return new RequestFactory($sJsClass, $c->g(Paginator::class));
        });

        // Register the user class
        $this->set($sClassName, function($c) use($sClassName, $sReflectionName) {
            $xRegisteredObject = $this->make($c->g($sReflectionName));
            // Initialize the object
            if($xRegisteredObject instanceof \Jaxon\CallableClass)
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
     * @param string $sClassName The package class name
     * @param array $aAppOptions The package options defined in the app section of the config file
     *
     * @return Config
     * @throws DataDepth
     */
    public function registerPackage(string $sClassName, array $aAppOptions): Config
    {
        $xAppConfig = $this->newConfig($aAppOptions);
        $this->val($sClassName . '_config', $xAppConfig);
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

    /**
     * Get a package config
     *
     * @param string $sClassName The package class name
     *
     * @return Config
     */
    public function getPackageConfig(string $sClassName): Config
    {
        return $this->g($sClassName . '_config');
    }
}
