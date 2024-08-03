<?php

/**
 * Exception.php - Jaxon exception
 *
 * This is the generic exception.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Exception;

class Exception extends \Exception
{
    public function __construct(string $sMessage)
    {
        parent::__construct($sMessage);
    }
}
