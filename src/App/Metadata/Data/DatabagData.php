<?php

/**
 * DatabagData.php
 *
 * Databag metadata for Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata\Data;

use Jaxon\Exception\SetupException;

use function array_map;
use function array_values;
use function preg_match;

class DatabagData extends AbstractData
{
    /**
     * The databag names
     *
     * @var array
     */
    protected $aNames = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'bags';
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return array_values($this->aNames);
    }

    /**
     * @param string $sName
     *
     * @return void
     */
    protected function validateName(string $sName): void
    {
        if(preg_match('/^[a-zA-Z][a-zA-Z0-9_\-\.]*$/', $sName) > 0)
        {
            return;
        }
        throw new SetupException("$sName is not a valid \"name\" value for databag");
    }

    /**
     * @param string $sName
     *
     * @return void
     */
    public function addValue(string $sName): void
    {
        $this->validateName($sName);

        $this->aNames[$sName] = $sName;
    }

    /**
     * @inheritDoc
     */
    public function encode(string $sVarName): array
    {
        return array_map(fn($sName) =>
            "{$sVarName}->addValue('$sName');", $this->aNames);
    }
}
