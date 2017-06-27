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
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Config;

class Json
{
    /**
     * Read and set Jaxon options from a JSON formatted config file
     *
     * @param string        $sConfigFile        The full path to the config file
     * @param string        $sLibKey            The key of the library options in the file
     * @param string|null   $sAppKey            The key of the application options in the file
     *
     * @return Jaxon\Utils\Config\Config
     */
    public static function read($sConfigFile, $sLibKey = '', $sAppKey = null)
    {
        $sConfigFile = realpath($sConfigFile);
        if(!is_readable($sConfigFile))
        {
            throw new \Jaxon\Exception\Config\File(jaxon_trans('config.errors.file.access', array('path' => $sConfigFile)));
        }
        $sFileContent = file_get_contents($sConfigFile);
        $aConfigOptions = json_decode($sFileContent, true);
        if(!is_array($aConfigOptions))
        {
            throw new \Jaxon\Exception\Config\File(jaxon_trans('config.errors.file.content', array('path' => $sConfigFile)));
        }

        // Setup the config options into the library.
        $jaxon = jaxon();
        $jaxon->setOptions($aConfigOptions, $sLibKey);
        $config = null;
        if(is_string($sAppKey))
        {
            $config = new Config();
            $config->setOptions($aConfigOptions, $sAppKey);
        }
        return $config;
    }
}
