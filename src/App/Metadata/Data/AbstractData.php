<?php

/**
 * AbstractData.php
 *
 * Base class for metadata for Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata\Data;

abstract class AbstractData
{
    /**
     * Generate the PHP code to populate a Metadata object
     *
     * @param string $sVarName
     *
     * @return array
     */
    abstract public function encode(string $sVarName): array;
}
