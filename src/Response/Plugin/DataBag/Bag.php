<?php

namespace Jaxon\Response\Plugin\DataBag;

use function is_array;

class Bag
{
    /**
     * @var array
     */
    protected $aData = [];

    /**
     * @var string
     */
    protected $sName = '';

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
        $this->aData = $aData;
    }

    /**
     * @return bool
     */
    public function touched()
    {
        return $this->bTouched;
    }

    /**
     * @param string $sName
     *
     * @return Bag
     */
    public function setName($sName)
    {
        $this->sName = $sName;
        return $this;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->aData;
    }

    /**
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return void
     */
    public function set($sKey, $xValue)
    {
        $this->bTouched = true;
        $this->aData[$this->sName][$sKey] = $xValue;
    }

    /**
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return mixed
     */
    public function get($sKey, $xValue = null)
    {
        return isset($this->aData[$this->sName][$sKey]) ? $this->aData[$this->sName][$sKey] : $xValue;
    }
}
