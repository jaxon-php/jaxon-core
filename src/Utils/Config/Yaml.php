<?php

/**
 * Yaml.php - Jaxon config reader
 *
 * Read the config data from a YAML formatted config file, save it locally
 * using the Config class, and then set the options in the library.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Config;

class Yaml
{
    /**
     * Read and set Jaxon options from a YAML formatted config file
     *
     * @param array         $sConfigFile        The full path to the config file
     * @param string        $sLibKeys           The keys of the library options in the file
     * @param string        $sAppKeys           The keys of the application options in the file
     *
     * @return Jaxon\Utils\Config\Config
     */
    public static function read($sConfigFile, $sLibKeys = '', $sAppKeys = null)
    {
        $sConfigFile = realpath($sConfigFile);
        if(!extension_loaded('yaml'))
        {
            throw new \Jaxon\Exception\Config\Yaml(jaxon_trans('config.errors.yaml.install'));
        }
        if(!is_readable($sConfigFile))
        {
            throw new \Jaxon\Exception\Config\File(jaxon_trans('config.errors.file.access', array('path' => $sConfigFile)));
        }
        $aConfigOptions = yaml_parse_file($sConfigFile);
        if(!is_array($aConfigOptions))
        {
            throw new \Jaxon\Exception\Config\File(jaxon_trans('config.errors.file.content', array('path' => $sConfigFile)));
        }

        // Setup the config options into the library.
        $jaxon = jaxon();
        $jaxon->setOptions($aConfigOptions, $sLibKeys);
        if(!is_string($sAppKeys))
        {
            return null;
        }
        $config = new Config();
        $config->setOptions($aConfigOptions, $sAppKeys);
        return $config;
    }
}
