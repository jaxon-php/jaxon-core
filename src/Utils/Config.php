<?php

/**
 * Config.php - Xajax config manager
 *
 * Read and set Xajax config options.
 *
 * @package xajax-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax\Utils;

class Config
{
    private $aOptions;

    public function __construct()
    {
        $this->aOptions = array();
    }

    /**
     * Set the value of a config option
     *
     * @param string        $sName            The option name
     * @param mixed            $sValue            The option value
     *
     * @return void
     */
    public function setOption($sName, $sValue)
    {
        $this->aOptions[$sName] = $sValue;
    }

    /**
     * Set the values of an array of config options
     *
     * @param array            $aOptions        The config options
     *
     * @return void
     */
    public function setOptions(array $aOptions)
    {
        $this->aOptions = array_merge($this->aOptions, $aOptions);
    }

    /**
     * Get the value of a config option
     *
     * @param string        $sName            The option name
     *
     * @return mixed        The option value, or null if the option is unknown
     */
    public function getOption($sName)
    {
        return (array_key_exists($sName, $this->aOptions) ? $this->aOptions[$sName] : null);
    }

    /**
     * Check the presence of a config option
     *
     * @param string        $sName            The option name
     *
     * @return bool        True if the option exists, and false if not
     */
    public function hasOption($sName)
    {
        return array_key_exists($sName, $this->aOptions);
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
        $sPrefix = (string)$sPrefix;
        $sPrefixLen = strlen($sPrefix);
        $aOptions = array();
        foreach($this->aOptions as $sName => $xValue)
        {
            if(substr($sName, 0, $sPrefixLen) == $sPrefix)
            {
                $aOptions[substr($sName, $sPrefixLen)] = $sName;
            }
        }
        return $aOptions;
    }
}
