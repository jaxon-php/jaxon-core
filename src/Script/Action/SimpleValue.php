<?php

/**
 * SimpleValue.php
 *
 * An simple value that will be inserted in the call as is.
 *
 * @package jaxon-core
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Script\Action;

class SimpleValue extends TypedValue
{
    /**
     * @param mixed $xValue
     */
    public function __construct(private mixed $xValue)
    {}

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return '_';
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->xValue;
    }
}
