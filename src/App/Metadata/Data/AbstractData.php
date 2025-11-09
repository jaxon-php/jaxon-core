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

use function preg_match;

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

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return mixed
     */
    abstract public function getValue(): mixed;

    /**
     * @param string $sMethod
     *
     * @return bool
     */
    protected function validateMethod(string $sMethod): bool
    {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $sMethod) > 0;
    }
}
