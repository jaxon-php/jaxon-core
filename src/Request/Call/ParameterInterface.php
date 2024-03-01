<?php

/**
 * ParameterInterface.php
 *
 * An interface for parameters to calls to Jaxon classes or functions.
 *
 * @package jaxon-core
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Call;

use JsonSerializable;

interface ParameterInterface extends JsonSerializable
{
    /**
     * Get the parameter type
     *
     * @return string
     */
    public function getType(): string;
}
