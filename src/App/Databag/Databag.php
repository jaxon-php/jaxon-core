<?php

namespace Jaxon\App\Databag;

use JsonSerializable;

use function key_exists;

class Databag implements JsonSerializable
{
    /**
     * @var bool
     */
    protected $bTouched = false;

    /**
     * The constructor
     *
     * @param array $aData
     */
    public function __construct(protected array $aData)
    {}

    /**
     * @return bool
     */
    public function touched(): bool
    {
        return $this->bTouched;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->aData;
    }

    /**
     * @param string $sBag
     *
     * @return void
     */
    public function clear(string $sBag): void
    {
        $this->bTouched = true;
        $this->aData[$sBag] = [];
    }

    /**
     * @param string $sBag
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return void
     */
    public function set(string $sBag, string $sKey, $xValue): void
    {
        $this->bTouched = true;
        $this->aData[$sBag][$sKey] = $xValue;
    }

    /**
     * @param string $sBag
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return void
     */
    public function new(string $sBag, string $sKey, $xValue): void
    {
        // Set the value only if it doesn't already exist.
        if(!isset($this->aData[$sBag]) || !key_exists($sKey, $this->aData[$sBag]))
        {
            $this->set($sBag, $sKey, $xValue);
        }
    }

    /**
     * @param string $sBag
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return mixed
     */
    public function get(string $sBag, string $sKey, $xValue = null): mixed
    {
        return $this->aData[$sBag][$sKey] ?? $xValue;
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->aData;
    }
}
