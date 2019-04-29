<?php

/**
 * Manager.php - Jaxon plugin manager
 *
 * Register Jaxon plugins, generate corresponding code, handle request
 * and redirect them to the right plugin.
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

namespace Jaxon\Plugin;

use Jaxon\Jaxon;
use Jaxon\Plugin\Package;
use Jaxon\Config\Config;

use Closure;

class Manager
{
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Cache;
    use \Jaxon\Utils\Traits\Event;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * All plugins, indexed by priority
     *
     * @var array
     */
    private $aPlugins = [];

    /**
     * Request plugins, indexed by name
     *
     * @var array
     */
    private $aRequestPlugins = [];

    /**
     * Response plugins, indexed by name
     *
     * @var array
     */
    private $aResponsePlugins = [];

    /**
     * An array of package names
     *
     * @var array
     */
    private $aPackages = [];

    /**
     * Get the request plugins
     *
     * @return array
     */
    public function getRequestPlugins()
    {
        return $this->aRequestPlugins;
    }

    /**
     * Get the response plugins
     *
     * @return array
     */
    public function getResponsePlugins()
    {
        return $this->aResponsePlugins;
    }

    /**
     * Get the package plugins
     *
     * @return array
     */
    public function getPackages()
    {
        return $this->aPackages;
    }

    /**
     * Inserts an entry into an array given the specified priority number
     *
     * If a plugin already exists with the given priority, the priority is automatically incremented until a free spot is found.
     * The plugin is then inserted into the empty spot in the array.
     *
     * @param Plugin         $xPlugin               An instance of a plugin
     * @param integer        $nPriority             The desired priority, used to order the plugins
     *
     * @return void
     */
    private function setPluginPriority(Plugin $xPlugin, $nPriority)
    {
        while (isset($this->aPlugins[$nPriority]))
        {
            $nPriority++;
        }
        $this->aPlugins[$nPriority] = $xPlugin;
        // Sort the array by ascending keys
        ksort($this->aPlugins);
    }

    /**
     * Register a plugin
     *
     * Below is a table for priorities and their description:
     * - 0 thru 999: Plugins that are part of or extensions to the jaxon core
     * - 1000 thru 8999: User created plugins, typically, these plugins don't care about order
     * - 9000 thru 9999: Plugins that generally need to be last or near the end of the plugin list
     *
     * @param Plugin         $xPlugin               An instance of a plugin
     * @param integer        $nPriority             The plugin priority, used to order the plugins
     *
     * @return void
     */
    public function registerPlugin(Plugin $xPlugin, $nPriority = 1000)
    {
        $bIsAlert = ($xPlugin instanceof \Jaxon\Dialog\Interfaces\Alert);
        $bIsConfirm = ($xPlugin instanceof \Jaxon\Dialog\Interfaces\Confirm);
        if($xPlugin instanceof Request)
        {
            // The name of a request plugin is used as key in the plugin table
            $this->aRequestPlugins[$xPlugin->getName()] = $xPlugin;
        }
        elseif($xPlugin instanceof Response)
        {
            // The name of a response plugin is used as key in the plugin table
            $this->aResponsePlugins[$xPlugin->getName()] = $xPlugin;
        }
        elseif(!$bIsConfirm && !$bIsAlert)
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.register.invalid', ['name' => get_class($xPlugin)]));
        }
        // This plugin implements the Alert interface
        if($bIsAlert)
        {
            jaxon()->dialog()->setAlert($xPlugin);
        }
        // This plugin implements the Confirm interface
        if($bIsConfirm)
        {
            jaxon()->dialog()->setConfirm($xPlugin);
        }
        // Register the plugin as an event listener
        if($xPlugin instanceof \Jaxon\Utils\Interfaces\EventListener)
        {
            $this->addEventListener($xPlugin);
        }

        $this->setPluginPriority($xPlugin, $nPriority);
    }

    /**
     * Register a package
     *
     * @param string         $sPackageClass         The package class name
     * @param Closure        $xClosure              A closure to create package instance
     *
     * @return void
     */
    public function registerPackage(string $sPackageClass, Closure $xClosure)
    {
        $this->aPackages[] = $sPackageClass;
        jaxon()->di()->set($sPackageClass, $xClosure);
    }

    /**
     * Register a function, event or callable object
     *
     * Call the request plugin with the $sType defined as name.
     *
     * @param string        $sType          The type of request handler being registered
     * @param string        $sCallable      The callable entity being registered
     * @param array|string  $aOptions       The associated options
     *
     * @return mixed
     */
    public function register($sType, $sCallable, $aOptions = [])
    {
        if(!key_exists($sType, $this->aRequestPlugins))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.register.plugin', ['name' => $sType]));
        }

        $xPlugin = $this->aRequestPlugins[$sType];
        return $xPlugin->register($sType, $sCallable, $aOptions);
        // foreach($this->aRequestPlugins as $xPlugin)
        // {
        //     if($mResult instanceof \Jaxon\Request\Request || is_array($mResult) || $mResult === true)
        //     {
        //         return $mResult;
        //     }
        // }
        // throw new \Jaxon\Exception\Error($this->trans('errors.register.method', ['args' => print_r($aArgs, true)]));
    }

    /**
     * Read and set Jaxon options from a JSON config file
     *
     * @param Config        $xAppConfig        The config options
     *
     * @return void
     */
    public function registerFromConfig($xAppConfig)
    {
        // Register user functions
        $aFunctionsConfig = $xAppConfig->getOption('functions', []);
        foreach($aFunctionsConfig as $xKey => $xValue)
        {
            if(is_integer($xKey) && is_string($xValue))
            {
                // Register a function without options
                $this->register(Jaxon::USER_FUNCTION, $xValue);
            }
            elseif(is_string($xKey) && is_array($xValue))
            {
                // Register a function with options
                $this->register(Jaxon::USER_FUNCTION, $xKey, $xValue);
            }
            else
            {
                continue;
                // Todo: throw an exception
            }
        }

        // Register classes and directories
        $aClassesConfig = $xAppConfig->getOption('classes', []);
        foreach($aClassesConfig as $xKey => $xValue)
        {
            if(is_integer($xKey) && is_string($xValue))
            {
                // Register a class without options
                $this->register(Jaxon::CALLABLE_CLASS, $xValue);
            }
            elseif(is_string($xKey) && is_array($xValue))
            {
                // Register a class with options
                $this->register(Jaxon::CALLABLE_CLASS, $xKey, $xValue);
            }
            elseif(is_integer($xKey) && is_array($xValue))
            {
                // The directory path is required
                if(!key_exists('directory', $xValue))
                {
                    continue;
                    // Todo: throw an exception
                }
                // Registering a directory
                $sDirectory = $xValue['directory'];
                $aOptions = [];
                if(key_exists('options', $xValue) &&
                    is_array($xValue['options']) || is_string($xValue['options']))
                {
                    $aOptions = $xValue['options'];
                }
                // Setup directory options
                if(key_exists('namespace', $xValue))
                {
                    $aOptions['namespace'] = $xValue['namespace'];
                }
                if(key_exists('separator', $xValue))
                {
                    $aOptions['separator'] = $xValue['separator'];
                }
                // Register a class without options
                $this->register(Jaxon::CALLABLE_DIR, $sDirectory, $aOptions);
            }
            else
            {
                continue;
                // Todo: throw an exception
            }
        }
    }


    /**
     * Find the specified response plugin by name and return a reference to it if one exists
     *
     * @param string        $sName                The name of the plugin
     *
     * @return \Jaxon\Plugin\Response
     */
    public function getResponsePlugin($sName)
    {
        if(array_key_exists($sName, $this->aResponsePlugins))
        {
            return $this->aResponsePlugins[$sName];
        }
        return null;
    }

    /**
     * Find the specified request plugin by name and return a reference to it if one exists
     *
     * @param string        $sName                The name of the plugin
     *
     * @return \Jaxon\Plugin\Request
     */
    public function getRequestPlugin($sName)
    {
        if(array_key_exists($sName, $this->aRequestPlugins))
        {
            return $this->aRequestPlugins[$sName];
        }
        return null;
    }
}
