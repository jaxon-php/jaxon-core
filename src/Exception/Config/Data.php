<?php

/**
 * Data.php - Incorrect config data exception
 *
 * This exception is thrown when config data are incorrect.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Exception\Config;

class Data extends \Exception
{
    public function __contruct($sMessage, $sKey, $nDepth = 0)
    {
        parent::__construct(jaxon_trans('config.errors.data.' . $sMessage,
            array('key' => $sKey, 'depth' => $nDepth)));
    }
}
