<?php

/**
 * HookData.php
 *
 * Hook metadata for Jaxon classes.
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
use function json_encode;

abstract class HookData extends AbstractData
{
    /**
     * @var array
     */
    protected $aCalls = [];

    /**
     * @return string
     */
    abstract protected function getType(): string;

    /**
     * @return string
     */
    public function getName(): string
    {
        return '__' . $this->getType();
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->aCalls;
    }

    /**
     * @param string $sMethod
     * @param array $aParams
     *
     * @return void
     */
    public function addCall(string $sMethod, array $aParams): void
    {
        if(!$this->validateMethod($sMethod))
        {
            $sType = $this->getType();
            throw new SetupException("'$sMethod' is not a valid \"call\" value for $sType.");
        }
        $this->aCalls[$sMethod] = $aParams;
    }

    /**
     * @inheritDoc
     */
    public function encode(string $sVarName): array
    {
        $aCalls = [];
        foreach($this->aCalls as $sMethod => $aParams)
        {
            $sParams = addslashes(json_encode($aParams));
            $aCalls[] = "{$sVarName}->addCall('$sMethod', json_decode(\"$sParams\", true));";
        }
        return $aCalls;
    }
}
