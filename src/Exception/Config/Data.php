<?php

/**
 * Data.php - Incorrect config data exception
 *
 * This exception is thrown when config data are incorrect.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Exception\Config;

class Data extends \Exception
{
    public function __construct($sMessage)
    {
        parent::__construct($sMessage);
    }
}
