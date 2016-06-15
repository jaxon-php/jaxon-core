<?php

/**
 * Yaml.php - Yaml-specific exception.
 *
 * This exception is thrown when an error related to Yaml occurs.
 * A typical example is when the php-yaml package is not installed.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Exception\Config;

class Yaml extends \Exception
{
    public function __contruct($sMessage)
    {
        parent::__construct(jaxon_trans('config.errors.yaml.' . $sMessage));
    }
}
