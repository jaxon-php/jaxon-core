<?php

/**
 * File.php - Unable to read config file.
 *
 * This exception is thrown when the config file cannot be read.
 *
 * @package xajax-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax\Config\Exception;

class File extends \Exception
{
    public function __contruct($sMessage, $sPath)
    {
        parent::__construct(xajax_trans('config.errors.file.' . $sMessage, array('path' => $sPath)));
    }
}
