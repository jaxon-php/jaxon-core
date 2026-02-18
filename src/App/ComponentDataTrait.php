<?php

namespace Jaxon\App;

use function array_key_exists;

trait ComponentDataTrait
{
    /**
     * @var array
     */
    private array $aComponentData = [];

    /**
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return static
     */
    public function set(string $sKey, mixed $xValue): static
    {
        $this->aComponentData[$sKey] = $xValue;
        return $this;
    }

    /**
     * @param string $sKey
     *
     * @return bool
     */
    public function has(string $sKey): bool
    {
        return array_key_exists($sKey, $this->aComponentData);
    }

    /**
     * @param string $sKey
     * @param mixed $xDefault
     *
     * @return mixed
     */
    public function get(string $sKey, mixed $xDefault = null): mixed
    {
        return $this->aComponentData[$sKey] ?? $xDefault;
    }
}
