<?php

/**
 * CallableRepository.php - Jaxon callable object repository
 *
 * This class stores all the callable object already created.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Support;

use Jaxon\Request\Factory\CallableClass\Request as RequestFactory;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CallableRepository
{
    /**
     * The classes
     *
     * These are all the registered classes.
     *
     * @var array
     */
    protected $aClasses = [];

    /**
     * The namespaces
     *
     * These are all the namespaces found in registered directories.
     *
     * @var array
     */
    protected $aNamespaces = [];

    /**
     * The created callable objects.
     *
     * @var array
     */
    protected $aCallableObjects = [];

    /**
     * The options to be applied to callable objects.
     *
     * @var array
     */
    protected $aCallableOptions = [];

    /**
     * Get a given class options from specified directory options
     *
     * @param string        $sClassName         The class name
     * @param array         $aClassOptions      The default class options
     * @param array         $aDirectoryOptions  The directory options
     *
     * @return array
     */
    public function makeClassOptions($sClassName, array $aClassOptions, array $aDirectoryOptions)
    {
        $aOptions = $aClassOptions;
        if(key_exists('separator', $aDirectoryOptions))
        {
            $aOptions['separator'] = $aDirectoryOptions['separator'];
        }
        if(key_exists('protected', $aDirectoryOptions))
        {
            $aOptions['protected'] = $aDirectoryOptions['protected'];
        }
        if(key_exists('*', $aDirectoryOptions))
        {
            $aOptions = array_merge($aOptions, $aDirectoryOptions['*']);
        }
        if(key_exists($sClassName, $aDirectoryOptions))
        {
            $aOptions = array_merge($aOptions, $aDirectoryOptions[$sClassName]);
        }

        // This value will be used to compute hash
        if(!key_exists('timestamp', $aOptions))
        {
            $aOptions['timestamp'] = 0;
        }

        return $aOptions;
    }

    /**
     *
     * @param string        $sClassName         The class name
     * @param array         $aClassOptions      The default class options
     * @param array         $aDirectoryOptions  The directory options
     *
     * @return void
     */
    public function addClass($sClassName, array $aClassOptions, array $aDirectoryOptions = [])
    {
        $this->aClasses[$sClassName] = $this->makeClassOptions($sClassName, $aClassOptions, $aDirectoryOptions);
    }

    /**
     *
     * @param string        $sNamespace     The namespace
     * @param array|string  $aOptions       The associated options
     *
     * @return void
     */
    public function addNamespace($sNamespace, $aOptions)
    {
        $this->aNamespaces[$sNamespace] = $aOptions;
    }

    /**
     * Find the options associated with a registered class name
     *
     * @param string        $sClassName            The class name
     *
     * @return array|null
     */
    public function getClassOptions($sClassName)
    {
        if(!key_exists($sClassName, $this->aClasses))
        {
            // Class not found
            return null;
        }
        return $this->aClasses[$sClassName];
    }

    /**
     * Find a callable object by class name
     *
     * @param string        $sClassName            The class name of the callable object
     *
     * @return CallableObject|null
     */
    public function getCallableObject($sClassName)
    {
        return isset($this->aCallableObjects[$sClassName]) ? $this->aCallableObjects[$sClassName] : null;
    }

    /**
     * Create a new callable object
     *
     * @param string        $sClassName            The class name of the callable object
     * @param array         $aOptions              The callable object options
     *
     * @return CallableObject|null
     */
    public function createCallableObject($sClassName, array $aOptions)
    {
        // Make sure the registered class exists
        if(key_exists('include', $aOptions))
        {
            require_once($aOptions['include']);
        }
        if(!class_exists($sClassName))
        {
            return null;
        }

        // Create the callable object
        $xCallableObject = new CallableObject($sClassName);
        $this->aCallableOptions[$sClassName] = [];
        foreach($aOptions as $sName => $xValue)
        {
            if(in_array($sName, ['separator', 'namespace', 'protected']))
            {
                $xCallableObject->configure($sName, $xValue);
            }
            elseif(is_array($xValue) && $sName != 'include')
            {
                // These options are to be included in javascript code.
                $this->aCallableOptions[$sClassName][$sName] = $xValue;
            }
        }
        $this->aCallableObjects[$sClassName] = $xCallableObject;

        // Register the request factory for this callable object
        jaxon()->di()->setCallableClassRequestFactory($sClassName, $xCallableObject);

        return $xCallableObject;
    }

    /**
     * Get all registered classes
     *
     * @return array
     */
    public function getClasses()
    {
        return $this->aClasses;
    }

    /**
     * Get all registered namespaces
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->aNamespaces;
    }

    /**
     * Get all registered callable objects
     *
     * @return array
     */
    public function getCallableObjects()
    {
        return $this->aCallableObjects;
    }

    /**
     * Get all registered callable objects options
     *
     * @return array
     */
    public function getCallableOptions()
    {
        return $this->aCallableOptions;
    }
}
