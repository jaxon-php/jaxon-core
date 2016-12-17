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
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Support;

use Jaxon\Request\Request;

class CallableObject
{
    use \Jaxon\Utils\ContainerTrait;

    /**
     * A reference to the callable object the user has registered
     *
     * @var object
     */
    private $callableObject;

    /**
     * The reflection class of the user registered callable object
     *
     * @var ReflectionClass
     */
    private $reflectionClass;
    
    /**
     * A list of methods of the user registered callable object the library must not export to javascript
     *
     * @var array
     */
    private $aExcludedMethods;
    
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
    
    public function __construct($obj)
    {
        $this->callableObject = $obj;
        $this->reflectionClass = new \ReflectionClass(get_class($this->callableObject));
        $this->aConfiguration = array();
        // By default, the methods of the RequestTrait and ResponseTrait traits are excluded
        $this->aExcludedMethods = array('setGlobalResponse', 'newResponse',
                'setJaxonCallable', 'getJaxonClassName', 'request');
    }

    /**
     * Return the class name of this callable object, without the namespace if any
     *
     * @return string
     */
    private function getClassName()
    {
        // Get the class name without the namespace.
        return $this->reflectionClass->getShortName();
    }

    /**
     * Return the name of this callable object
     *
     * This is the name of the generated javascript class.
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
            $name = $this->classpath . $this->separator . $name;
        }
        return $name;
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
        return rtrim($this->classpath, $this->separator);
    }

    /**
     * Return a list of methods of the callable object to export to javascript
     *
     * @return array
     */
    public function getMethods()
    {
        $aReturn = array();
        foreach($this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $sMethodName = $xMethod->getShortName();
            // Don't take magic __call, __construct, __destruct methods
            if(strlen($sMethodName) > 2 && substr($sMethodName, 0, 2) == '__')
            {
                continue;
            }
            // Don't take excluded methods
            if(in_array($sMethodName, $this->aExcludedMethods))
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
                $this->classpath = $sValue;
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
        if($sName == 'excluded')
        {
            if(is_array($sValue))
                $this->aExcludedMethods = array_merge($this->aExcludedMethods, $sValue);
            else if(is_string($sValue))
                $this->aExcludedMethods[] = $sValue;
            return;
        }
        
        if(!isset($this->aConfiguration[$sMethod]))
        {
            $this->aConfiguration[$sMethod] = array();
        }    
        $this->aConfiguration[$sMethod][$sName] = $sValue;
    }

    /**
     * Produce an array of <Jaxon\Request\Request>, one for each method exposed by this callable object
     *
     * @return array
     */
    public function generateRequests()
    {
        $aRequests = array();
        $sClass = $this->getName();

        foreach($this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $sMethodName = $xMethod->getShortName();
            // Don't generate magic __call, __construct, __destruct methods
            if(strlen($sMethodName) > 2 && substr($sMethodName, 0, 2) == '__')
            {
                continue;
            }
            // Don't generate excluded methods
            if(in_array($sMethodName, $this->aExcludedMethods))
            {
                continue;
            }
            $aRequests[$sMethodName] = new Request($sClass . '.' . $sMethodName, 'object');
        }

        return $aRequests;
    }
    
    /**
     * Generate client side javascript code for calls to all methods exposed by this callable object
     *
     * @return string
     */
    public function getScript()
    {
        $sJaxonPrefix = $this->getOption('core.prefix.class');
        $sClass = $this->getName();
        $aMethods = array();

        // Common options to be set on all methods
        $aCommonConfig = array_key_exists('*', $this->aConfiguration) ? $this->aConfiguration['*'] : array();
        foreach($this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $sMethodName = $xMethod->getShortName();
            // Don't export magic __call, __construct, __destruct methods
            if(strlen($sMethodName) > 0 && substr($sMethodName, 0, 2) == '__')
            {
                continue;
            }
            // Don't export "excluded" methods
            if(in_array($sMethodName, $this->aExcludedMethods))
            {
                continue;
            }
            // Specific options for this method
            $aMethodConfig = array_key_exists($sMethodName, $this->aConfiguration) ?
                array_merge($aCommonConfig, $this->aConfiguration[$sMethodName]) : $aCommonConfig;
            $aMethod = array('name' => $sMethodName, 'config' => $aMethodConfig);
            $aMethods[] = $aMethod;
        }

        return $this->render('support/object.js.tpl', array(
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
     * @param array         $aArgs                The arguments to pass to the method
     *
     * @return void
     */
    public function call($sMethod, $aArgs)
    {
        if(!$this->hasMethod($sMethod))
            return;
        $reflectionMethod = $this->reflectionClass->getMethod($sMethod);
        $this->getResponseManager()->append($reflectionMethod->invokeArgs($this->callableObject, $aArgs));
    }

    /**
     * Return the registered callable object
     *
     * @return object
     */
    public function getRegisteredObject()
    {
        return $this->callableObject;
    }
}
