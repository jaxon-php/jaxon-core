<?php

/**
 * ExcludeData.php
 *
 * Exclude metadata for Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata\Data;

class ExcludeData extends AbstractData
{
    /**
     * @var bool
     */
    private bool $bValue = true;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'protected';
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->bValue;
    }

    /**
     * @param bool $bValue
     *
     * @return void
     */
    public function setValue(bool $bValue): void
    {
        $this->bValue = $bValue;
    }

    /**
     * @inheritDoc
     */
    public function encode(string $sVarName): array
    {
        return ["{$sVarName}->setValue(" . ($this->bValue ? 'true' : 'false') . ");"];
    }
}
