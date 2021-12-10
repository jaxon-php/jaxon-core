<?php

/**
 * Php.php - Jaxon config reader
 *
 * Read the config data from a PHP config file.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Config;

class Php
{
    /**
     * Read options from a PHP config file
     *
     * @param string $sConfigFile The full path to the config file
     *
     * @return array
     * @throws Exception\File
     */
    public static function read($sConfigFile)
    {
        $sConfigFile = realpath($sConfigFile);
        if(!is_readable($sConfigFile))
        {
            throw new Exception\File(jaxon_trans('errors.file.access', ['path' => $sConfigFile]));
        }
        $aConfigOptions = include($sConfigFile);
        if(!is_array($aConfigOptions))
        {
            throw new Exception\File(jaxon_trans('errors.file.content', ['path' => $sConfigFile]));
        }

        return $aConfigOptions;
    }
}
