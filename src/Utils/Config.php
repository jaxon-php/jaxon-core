<?php

/**
 * Config.php - Jaxon config manager
 *
 * Read and set Jaxon config options.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils;

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
     * Recursively set Jaxon options from a data array
     *
     * @param array         $aOptions           The options array
     * @param string        $sPrefix            The prefix for option names
     * @param integer       $nDepth             The depth from the first call
     *
     * @return void
     */
    private function _setOptions(array $aOptions, $sPrefix = '', $nDepth = 0)
    {
        $sPrefix = (string)$sPrefix;
        $nDepth = intval($nDepth);
        // Check the max depth
        if($nDepth < 0 || $nDepth > 5)
        {
            throw new \Jaxon\Exception\Config\Data('depth', $sPrefix, $nDepth);
        }
        foreach ($aOptions as $sName => $xOption)
        {
            if(is_array($xOption))
            {
                // Recursively read the options in the array
                $this->_setOptions($xOption, $sPrefix . $sName . '.', $nDepth + 1);
            }
            else if(is_string($xOption) || is_numeric($xOption) || is_bool($xOption))
            {
                // Save the value of this option
                $this->aOptions[$sPrefix . $sName] = $xOption;
            }
        }
    }

    /**
     * Set the values of an array of config options
     *
     * @param array         $aOptions           The options array
     * @param string        $sKeys              The keys of the options in the array
     *
     * @return void
     */
    public function setOptions(array $aOptions, $sKeys = '')
    {
        // Find the config array in the input data
        $aKeys = explode('.', (string)$sKeys);
        foreach ($aKeys as $sKey)
        {
            if(($sKey))
            {
                if(!array_key_exists($sKey, $aOptions) || !is_array($aOptions[$sKey]))
                {
                    throw new \Jaxon\Exception\Config\Data('missing', $sKeys);
                }
                $aOptions = $aOptions[$sKey];
            }
        }
        $this->_setOptions($aOptions);
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
