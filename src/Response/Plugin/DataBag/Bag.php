<?php

namespace Jaxon\Response\Plugin\DataBag;

use function is_array;
use function array_map;

class Bag
{
    /**
     * @var array
     */
    protected $aData = [];

    /**
     * @var boolean
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
    public function touched()
    {
        return $this->bTouched;
    }

    /**
     * @return array
     */
    public function getAll()
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
    public function set($sBag, $sKey, $xValue)
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
    public function get($sBag, $sKey, $xValue = null)
    {
        return isset($this->aData[$sBag][$sKey]) ? $this->aData[$sBag][$sKey] : $xValue;
    }
}
