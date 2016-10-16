<?php

/**
 * Json.php - Jaxon config reader
 *
 * Read the config data from a JSON formatted config file, save it locally
 * using the Config class, and then set the options in the library.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Config;

class Json
{
    /**
     * Read and set Jaxon options from a JSON formatted config file
     *
     * @param array         $sConfigFile        The full path to the config file
     * @param string        $sKeys              The keys of the options in the file
     *
     * @return array
     */
    public static function read($sConfigFile, $sKeys = '')
    {
        $sConfigFile = realpath($sConfigFile);
        if(!is_readable($sConfigFile))
        {
            throw new \Jaxon\Exception\Config\File('access', $sConfigFile);
        }
        $sFileContent = file_get_contents($sConfigFile);
        $aConfigOptions = json_decode($sFileContent, true);
        if(!is_array($aConfigOptions))
        {
            throw new \Jaxon\Exception\Config\File('content', $sConfigFile);
        }

        // Setup the config options into the library.
        $jaxon = jaxon();
        $jaxon->setOptions($aConfigOptions, $sKeys);
        return $aConfigOptions;
    }
}
