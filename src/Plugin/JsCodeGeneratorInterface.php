<?php

/**
 * JsCodeGeneratorInterface.php
 *
 * Any class generating js code must implement this interface.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin;

interface JsCodeGeneratorInterface
{
    /**
     * Get the value to be hashed
     *
     * @return string
     */
    public function getHash(): string;

    /**
     * @return JsCode
     */
    public function getJsCode(): JsCode;
}
