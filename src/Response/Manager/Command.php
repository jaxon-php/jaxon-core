<?php

/**
 * Command.php
 *
 * This class represents a command in a Jaxon response.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response\Manager;

use ArrayAccess;
use JsonSerializable;

use function trim;

class Command implements ArrayAccess, JsonSerializable
{
    /**
     * @var array
     */
    private $aCommand;

    public function __construct(array $aCommand)
    {
        $this->aCommand = $aCommand;
    }

    /**
     * Set a component on the command
     *
     * @param array $aComponent
     *
     * @return Command
     */
    public function setComponent(array $aComponent): Command
    {
        $this->aCommand['component'] = $aComponent;
        return $this;
    }

    /**
     * Convert to string
     *
     * @param mixed $xData
     *
     * @return string
     */
    private function str($xData): string
    {
        return trim((string)$xData, " \t\n");
    }

    /**
     * Add an option to the command
     *
     * @param string $sName    The option name
     * @param string|array|JsonSerializable $xValue    The option value
     *
     * @return Command
     */
    public function setOption(string $sName, string|array|JsonSerializable $xValue): Command
    {
        if(isset($this->aCommand['options']))
        {
            $this->aCommand['options'][$this->str($sName)] = $xValue;
            return $this;
        }
        $this->aCommand['options'] = [$this->str($sName) => $xValue];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return $this->aCommand;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->aCommand[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): mixed
    {
        return $this->aCommand[$offset] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {} // Not implemented

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {} // Not implemented
}
