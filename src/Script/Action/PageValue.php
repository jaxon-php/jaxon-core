<?php

/**
 * PageValue.php
 *
 * The value for page numbers.
 *
 * @package jaxon-core
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Script\Action;

class PageValue extends TypedValue
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return 'page';
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return ['_type' => 'page', '_name' => ''];
    }
}
