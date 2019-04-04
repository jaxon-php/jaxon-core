<?php

/**
 * CallableObject.php - Jaxon callable object
 *
 * This class stores a reference to an object whose methods can be called from
 * the client via an Jaxon request
 *
 * The Jaxon plugin manager will call <CallableObject->getClientScript> so that
 * stub functions can be generated and sent to the browser.
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

namespace Jaxon\Request\Support;

use Jaxon\Request\Request;

class CallableObject
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Template;
    use \Jaxon\Utils\Traits\DI;

    /**
     * A reference to the callable object the user has registered
     *
     * @var object
     */
    private $callableObject;

    /**
     * The reflection class of the user registered callable object
     *
     * @var \ReflectionClass
     */
    private $reflectionClass;

    /**
     * A list of methods of the user registered callable object the library must not export to javascript
     *
     * @var array
     */
    private $aProtectedMethods;

    /**
     * The namespace where the callable object class is defined
     *
     * @var string
     */
    private $namespace = '';

    /**
     * The path to the directory where the callable object class is defined, starting from the namespace root
     *
     * @var string
     */
    private $classpath = '';

    /**
     * The character to use as separator in javascript class names
     *
     * @var string
     */
    private $separator = '.';

    /**
     * An associative array that will contain configuration options for zero or more of the objects methods
     *
     * These configuration options will define the call options for each request.
     * The call options will be passed to the client browser when the function stubs are generated.
     *
     * @var array
     */
    private $aConfiguration;

    /**
     * The class constructor
     *
     * @param object|string            $xCallable               The callable object instance or class name
     *
     */
    public function __construct($xCallable)
    {
        if(is_string($xCallable)) // Received a class name
        {
            $this->reflectionClass = new \ReflectionClass($xCallable);
            $this->callableObject = null;
        }
        else // if(is_object($xCallable)) // Received a class instance
        {
            $this->reflectionClass = new \ReflectionClass(get_class($xCallable));
            $this->setCallable($xCallable);
        }
        $this->aConfiguration = [];
        // By default, no method is "protected"
        $this->aProtectedMethods = [];
    }

    /**
     * Set a user registered callable object.
     *
     * If the input parameter is null, the callable is first created with its reflection object.
     *
     * @param object|null           $xCallable          The callable object instance or null
     *
     * @return void
     */
    private function setCallable($xCallable = null)
    {
        if($xCallable == null)
        {
            // Use the Reflection class to get the parameters of the constructor
            if(($constructor = $this->reflectionClass->getConstructor()) != null)
            {
                $parameters = $constructor->getParameters();
                $parameterInstances = [];
                foreach($parameters as $parameter)
                {
                    // Get the parameter instance from the DI
                    $parameterInstances[] = $this->diGet($parameter->getClass()->getName());
                }
                $xCallable = $this->reflectionClass->newInstanceArgs($parameterInstances);
            }
            else
            {
                $xCallable = $this->reflectionClass->newInstance();
            }
        }
        // Save the Jaxon callable object into the user callable object
        if($this->reflectionClass->hasMethod('setJaxonCallable'))
        {
            $xCallable->setJaxonCallable($this);
        }
        $this->callableObject = $xCallable;
    }

    /**
     * Return the registered callable object
     *
     * @return object
     */
    public function getRegisteredObject()
    {
        if($this->callableObject == null)
        {
            $this->setCallable();
        }
        return $this->callableObject;
    }

    /**
     * Return the class name of this callable object, without the namespace if any
     *
     * @return string
     */
    public function getClassName()
    {
        // Get the class name without the namespace.
        return $this->reflectionClass->getShortName();
    }

    /**
     * Return the name of this callable object
     *
     * @return string
     */
    public function getName()
    {
        // The class name without the namespace.
        $name = $this->reflectionClass->getShortName();
        // Append the classpath to the name
        if(($this->classpath))
        {
            $name = $this->classpath . '\\' . $name;
        }
        return $name;
    }

    /**
     * Return the javascript name of this callable object
     *
     * @return string
     */
    public function getJsName()
    {
        return str_replace('\\', $this->separator, $this->getName());
    }

    /**
     * Return the namespace of this callable object
     *
     * @return string
     */
    public function getNamespace()
    {
        // The namespace the class was registered with.
        return $this->namespace;
    }

    /**
     * Return the class path of this callable object
     *
     * @return string
     */
    public function getPath()
    {
        // The class path without the trailing separator.
        return $this->classpath;
    }

    /**
     * Return a list of methods of the callable object to export to javascript
     *
     * @return array
     */
    public function getMethods()
    {
        $aReturn = [];
        foreach($this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $sMethodName = $xMethod->getShortName();
            // Don't take magic __call, __construct, __destruct methods
            if(strlen($sMethodName) > 2 && substr($sMethodName, 0, 2) == '__')
            {
                continue;
            }
            // Don't take excluded methods
            if(in_array($sMethodName, $this->aProtectedMethods))
            {
                continue;
            }
            $aReturn[] = $sMethodName;
        }
        return $aReturn;
    }

    /**
     * Set configuration options / call options for each method
     *
     * @param string        $sMethod            The name of the method
     * @param string        $sName                The name of the configuration option
     * @param string        $sValue                The value of the configuration option
     *
     * @return void
     */
    public function configure($sMethod, $sName, $sValue)
    {
        // Set the namespace
        if($sName == 'namespace')
        {
            if($sValue != '')
                $this->namespace = $sValue;
            return;
        }
        // Set the classpath
        if($sName == 'classpath')
        {
            if($sValue != '')
                $this->classpath = trim($sValue, '\\');
            return;
        }
        // Set the separator
        if($sName == 'separator')
        {
            if($sValue == '_' || $sValue == '.')
                $this->separator = $sValue;
            return;
        }
        // Set the excluded methods
        if($sName == 'protected')
        {
            if(is_array($sValue))
                $this->aProtectedMethods = array_merge($this->aProtectedMethods, $sValue);
            elseif(is_string($sValue))
                $this->aProtectedMethods[] = $sValue;
            return;
        }

        if(!isset($this->aConfiguration[$sMethod]))
        {
            $this->aConfiguration[$sMethod] = [];
        }
        $this->aConfiguration[$sMethod][$sName] = $sValue;
    }

    /**
     * Generate client side javascript code for calls to all methods exposed by this callable object
     *
     * @return string
     */
    public function getScript()
    {
        $sJaxonPrefix = $this->getOption('core.prefix.class');
        // "\" are replaced with the configured separator in the generated javascript code.
        $sClass = str_replace('\\', $this->separator, $this->getName());
        $aMethods = [];

        // Common options to be set on all methods
        $aCommonConfig = array_key_exists('*', $this->aConfiguration) ? $this->aConfiguration['*'] : [];
        foreach($this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $sMethodName = $xMethod->getShortName();
            // Don't export magic __call, __construct, __destruct methods
            if(strlen($sMethodName) > 0 && substr($sMethodName, 0, 2) == '__')
            {
                continue;
            }
            // Don't export "protected" methods
            if(in_array($sMethodName, $this->aProtectedMethods))
            {
                continue;
            }
            // Specific options for this method
            $aMethodConfig = array_key_exists($sMethodName, $this->aConfiguration) ?
                array_merge($aCommonConfig, $this->aConfiguration[$sMethodName]) : $aCommonConfig;
            $aMethod = array('name' => $sMethodName, 'config' => $aMethodConfig);
            $aMethods[] = $aMethod;
        }

        return $this->render('jaxon::support/object.js', array(
            'sPrefix' => $sJaxonPrefix,
            'sClass' => $sClass,
            'aMethods' => $aMethods,
        ));
    }

    /**
     * Check if the specified class name matches the class name of the registered callable object
     *
     * @return boolean
     */
    public function isClass($sClass)
    {
        return ($this->reflectionClass->getName() === $sClass);
    }

    /**
     * Check if the specified method name is one of the methods of the registered callable object
     *
     * @param string        $sMethod            The name of the method to check
     *
     * @return boolean
     */
    public function hasMethod($sMethod)
    {
        return $this->reflectionClass->hasMethod($sMethod) || $this->reflectionClass->hasMethod('__call');
    }

    /**
     * Call the specified method of the registered callable object using the specified array of arguments
     *
     * @param string        $sMethod            The name of the method to call
     * @param array         $aArgs              The arguments to pass to the method
     *
     * @return void
     */
    public function call($sMethod, $aArgs)
    {
        if(!$this->hasMethod($sMethod))
        {
            return;
        }
        $reflectionMethod = $this->reflectionClass->getMethod($sMethod);
        $callableObject = $this->getRegisteredObject();
        $response = $reflectionMethod->invokeArgs($callableObject, $aArgs);
        if(($response))
        {
            $this->getResponseManager()->append($response);
        }
    }
}
