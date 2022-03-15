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

interface ParameterInterface
{
    /**
     * Generate the javascript code of the parameter.
     *
     * @return string
     */
    public function getScript(): string;
}
