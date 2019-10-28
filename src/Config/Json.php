<?php

/**
 * Json.php - Jaxon config reader
 *
 * Read the config data from a JSON formatted config file.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Config;

class Json
{
    /**
     * Read options from a JSON formatted config file
     *
     * @param string        $sConfigFile        The full path to the config file
     *
     * @return array
     */
    public static function read($sConfigFile)
    {
        $sConfigFile = realpath($sConfigFile);
        if(!is_readable($sConfigFile))
        {
            throw new \Jaxon\Config\Exception\File(jaxon_trans('config.errors.file.access', ['path' => $sConfigFile]));
        }
        $sFileContent = file_get_contents($sConfigFile);
        $aConfigOptions = json_decode($sFileContent, true);
        if(!is_array($aConfigOptions))
        {
            throw new \Jaxon\Config\Exception\File(jaxon_trans('config.errors.file.content', ['path' => $sConfigFile]));
        }

        return $aConfigOptions;
    }
}
