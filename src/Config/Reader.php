<?php

/**
 * Reader.php - Jaxon config reader
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Config;

class Reader
{
    /**
     * Read options from a config file
     *
     * @param string        $sConfigFile        The full path to the config file
     *
     * @return array
     */
    public function read($sConfigFile)
    {
        $sExt = pathinfo($sConfigFile, PATHINFO_EXTENSION);
        switch($sExt)
        {
        case 'php':
            $aConfigOptions = Php::read($sConfigFile);
            break;
        case 'yaml':
        case 'yml':
            $aConfigOptions = Yaml::read($sConfigFile);
            break;
        case 'json':
            $aConfigOptions = Json::read($sConfigFile);
            break;
        default:
            $sErrorMsg = jaxon_trans('config.errors.file.extension', array('path' => $sConfigFile));
            throw new \Jaxon\Config\Exception\File($sErrorMsg);
        }
        return $aConfigOptions;
    }
}
