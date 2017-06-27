<?php

/**
 * Config.php - Trait for config functions
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Traits;

use Jaxon\Utils\Container;

trait Config
{
    /**
     * Get the Jaxon library version number
     *
     * @return string        The version number
     */
    public function getVersion()
    {
        return Container::getInstance()->getVersion();
    }

    /**
     * Set the value of a config option
     *
     * @param string        $sName                The option name
     * @param mixed         $sValue               The option value
     *
     * @return void
     */
    public function setOption($sName, $sValue)
    {
        return Container::getInstance()->getConfig()->setOption($sName, $sValue);
    }
    
    /**
     * Set the values of an array of config options
     *
     * @param array         $aOptions           The config options
     * @param string        $sKeys              The keys of the options in the array
     *
     * @return void
     */
    public function setOptions($aOptions, $sKeys = '')
    {
        return Container::getInstance()->getConfig()->setOptions($aOptions, $sKeys);
    }

    /**
     * Get the value of a config option
     *
     * @param string        $sName              The option name
     * @param mixed         $xDefault           The default value, to be returned if the option is not defined
     *
     * @return mixed        The option value, or null if the option is unknown
     */
    public function getOption($sName, $xDefault = null)
    {
        return Container::getInstance()->getConfig()->getOption($sName, $xDefault);
    }

    /**
     * Check the presence of a config option
     *
     * @param string        $sName              The option name
     *
     * @return bool        True if the option exists, and false if not
     */
    public function hasOption($sName)
    {
        return Container::getInstance()->getConfig()->hasOption($sName);
    }

    /**
     * Get the names of the options matching a given prefix
     *
     * @param string        $sPrefix        The prefix to match
     *
     * @return array        The options matching the prefix
     */
    public function getOptionNames($sPrefix)
    {
        return Container::getInstance()->getConfig()->getOptionNames($sPrefix);
    }

    /**
     * Create a new the config manager
     *
     * @return Jaxon\Utils\Config\Config            The config manager
     */
    public function newConfig()
    {
        return Container::getInstance()->newConfig();
    }
}
