<?php

/**
 * File.php - Unable to read config file.
 *
 * This exception is thrown when the config file cannot be read.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Exception\Config;

class File extends \Exception
{
    public function __contruct($sMessage, $sPath)
    {
        parent::__construct(jaxon_trans('config.errors.file.' . $sMessage, array('path' => $sPath)));
    }
}
