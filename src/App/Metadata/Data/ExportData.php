<?php

/**
 * ExportData.php
 *
 * Export metadata for Jaxon classes.
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
use function is_string;
use function json_encode;

class ExportData extends AbstractData
{
    /**
     * @var array<string, array<string>>
     */
    private array $aMethods = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'export';
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->aMethods;
    }

    /**
     * @param array $aMethods
     *
     * @return void
     */
    public function setMethods(array $aMethods): void
    {
        foreach(['base', 'only', 'except'] as $sKey)
        {
            foreach($aMethods[$sKey] ?? [] as $sMethod)
            {
                if(!is_string($sMethod) || !$this->validateMethod($sMethod))
                {
                    throw new SetupException("'$sMethod' is not a valid method name.");
                }
            }
        }
        $this->aMethods = $aMethods;
    }

    /**
     * @inheritDoc
     */
    public function encode(string $sVarName): array
    {
        $sMethods = addslashes(json_encode($this->aMethods));
        return ["{$sVarName}->setMethods(json_decode(\"$sMethods\", true));"];
    }
}
