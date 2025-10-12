<?php

/**
 * BeforeData.php
 *
 * Before metadata for Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata\Data;

class BeforeData extends HookData
{
    /**
     * @inheritDoc
     */
    protected function getType(): string
    {
        return 'before';
    }
}
