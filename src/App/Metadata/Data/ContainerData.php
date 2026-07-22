<?php

/**
 * ContainerData.php
 *
 * Container metadata for Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata\Data;

use Jaxon\Exception\SetupException;

use function addslashes;
use function preg_match;

class ContainerData extends AbstractData
{
    /**
     * The properties to get from the container
     *
     * @var array
     */
    protected $aProperties = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return '__di';
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->aProperties;
    }

    /**
     * @param string $sAttr
     *
     * @return void
     */
    protected function validateAttr(string $sAttr): void
    {
        if(preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $sAttr) > 0)
        {
            return;
        }
        throw new SetupException("$sAttr is not a valid \"attr\" value for di");
    }

    /**
     * @param string $sClass
     *
     * @return void
     */
    protected function validateClass(string $sClass): void
    {
        if(preg_match('/^(\\\)?([a-zA-Z][a-zA-Z0-9_]*)(\\\[a-zA-Z][a-zA-Z0-9_]*)*$/', $sClass) > 0)
        {
            return;
        }
        throw new SetupException("$sClass is not a valid \"class\" value for di");
    }

    /**
     * @param string $sAttr
     * @param string $sClass
     * @param string $sDeclaringClass
     *
     * @return void
     */
    public function addValue(string $sAttr, string $sClass, string $sDeclaringClass): void
    {
        $this->validateAttr($sAttr);
        $this->validateClass($sClass);
        $this->validateClass($sDeclaringClass);

        // Allow the setter to access private and protected attributes.
        $cSetter = (static fn($xComponent, $sAttr, $xDiValue) =>
            $xComponent->$sAttr = $xDiValue)->bindTo(null, $sDeclaringClass);
        $this->aProperties[$sAttr] = [$sClass, $sDeclaringClass, $cSetter];
    }

    /**
     * @inheritDoc
     */
    public function encode(string $sVarName): array
    {
        $aCalls = [];
        foreach($this->aProperties as $sAttr => [$sClass, $sDeclaringClass])
        {
            $aCalls[] = "{$sVarName}->addValue('$sAttr', '" .
                addslashes($sClass) . "', '" . addslashes($sDeclaringClass) . "');";
        }
        return $aCalls;
    }
}
