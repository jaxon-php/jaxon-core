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

namespace Jaxon\Features;

trait Config
{
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
        return jaxon()->di()->getConfig()->setOption($sName, $sValue);
    }

    /**
     * Get the value of a config option
     *
     * @param string        $sName              The option name
     * @param mixed|null    $xDefault           The default value, to be returned if the option is not defined
     *
     * @return mixed        The option value, or null if the option is unknown
     */
    public function getOption($sName, $xDefault = null)
    {
        return jaxon()->di()->getConfig()->getOption($sName, $xDefault);
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
        return jaxon()->di()->getConfig()->hasOption($sName);
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
        return jaxon()->di()->getConfig()->getOptionNames($sPrefix);
    }

    /**
     * Create a new the config manager
     *
     * @return \Jaxon\Utils\Config\Config            The config manager
     */
    public function newConfig()
    {
        return jaxon()->di()->newConfig();
    }
}
