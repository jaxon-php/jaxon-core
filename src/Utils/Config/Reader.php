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

namespace Jaxon\Utils\Config;

class Reader
{
    /**
     * Read options from a config file
     *
     * @param string $sConfigFile The full path to the config file
     *
     * @return array
     * @throws Exception\File
     * @throws Exception\Yaml
     */
    public function read($sConfigFile)
    {
        if(!($sConfigFile = trim($sConfigFile)))
        {
            return [];
        }

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
            $sErrorMsg = jaxon_trans('errors.file.extension', ['path' => $sConfigFile]);
            throw new Exception\File($sErrorMsg);
        }

        return $aConfigOptions;
    }

    /**
     * Read options from a config file and setup the library
     *
     * @param string $sConfigFile The full path to the config file
     * @param string $sConfigSection The section of the config file to be loaded
     *
     * @return void
     * @throws Exception\File
     * @throws Exception\Yaml
     * @throws Exception\Data
     */
    public function load($sConfigFile, $sConfigSection = '')
    {
        $aConfigOptions = $this->read($sConfigFile);
        // Set up the lib config options.
        jaxon()->di()->getConfig()->setOptions($aConfigOptions, $sConfigSection);
    }
}
