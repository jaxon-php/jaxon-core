<?php

namespace Jaxon\Plugin\Response\DataBag;

use function array_map;
use function is_array;

class DataBag
{
    /**
     * @var array
     */
    protected $aData = [];

    /**
     * @var bool
     */
    protected $bTouched = false;

    /**
     * The constructor
     *
     * @param array $aData
     */
    public function __construct(array $aData)
    {
        // Ensure all contents are arrays.
        $this->aData = array_map(function($aValue) {
            return is_array($aValue) ? $aValue : [];
        }, $aData);
    }

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
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return void
     */
    public function set(string $sBag, string $sKey, $xValue)
    {
        $this->bTouched = true;
        $this->aData[$sBag][$sKey] = $xValue;
    }

    /**
     * @param string $sBag
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return mixed
     */
    public function get(string $sBag, string $sKey, $xValue = null)
    {
        return $this->aData[$sBag][$sKey] ?? $xValue;
    }
}
