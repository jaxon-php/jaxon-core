<?php

/**
 * TypedValue.php
 *
 * Generic class for any value with a type.
 *
 * @package jaxon-core
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Script\Action;

use JsonSerializable;

use function is_a;

abstract class TypedValue implements JsonSerializable
{
    /**
     * @return string
     */
    abstract public function getType(): string;

    /**
     * @param mixed $xValue
     *
     * @return TypedValue
     */
    public static function make(mixed $xValue): TypedValue
    {
        return $xValue instanceof TypedValue ? $xValue : new SimpleValue($xValue);
    }

    /**
     * @return PageValue
     */
    public static function page(): PageValue
    {
        return new PageValue();
    }

    /**
     * @param mixed $xValue
     *
     * @return bool
     */
    public static function isPage(mixed $xValue): bool
    {
        return is_a($xValue, PageValue::class);
    }
}
