<?php

/**
 * jaxon_fn.php
 *
 * The Jaxon global function without namespace.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Ajax\Jaxon;

/**
 * Return the single instance of the Jaxon class
 *
 * @return Jaxon
 */
function jaxon(): Jaxon
{
    return Jaxon::getInstance();
}
