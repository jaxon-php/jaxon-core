<?php

/**
 * Yaml.php - Jaxon config reader
 *
 * Read the config data from a YAML formatted config file.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Config;

class Yaml
{
    /**
     * Read options from a YAML formatted config file
     *
     * @param string        $sConfigFile        The full path to the config file
     *
     * @return array
     */
    public static function read($sConfigFile)
    {
        $sConfigFile = realpath($sConfigFile);
        if(!extension_loaded('yaml'))
        {
            throw new \Jaxon\Config\Exception\Yaml(jaxon_trans('config.errors.yaml.install'));
        }
        if(!is_readable($sConfigFile))
        {
            throw new \Jaxon\Config\Exception\File(jaxon_trans('config.errors.file.access', array('path' => $sConfigFile)));
        }
        $aConfigOptions = yaml_parse_file($sConfigFile);
        if(!is_array($aConfigOptions))
        {
            throw new \Jaxon\Config\Exception\File(jaxon_trans('config.errors.file.content', array('path' => $sConfigFile)));
        }

        return $aConfigOptions;
    }
}
