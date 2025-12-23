<?php

/**
 * CssCodeGeneratorInterface.php
 *
 * Any class generating css code must implement this interface.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin;

interface CssCodeGeneratorInterface
{
    /**
     * Get the value to be hashed
     *
     * @return string
     */
    public function getHash(): string;

    /**
     * @return CssCode
     */
    public function getCssCode(): CssCode;
}
