<?php

namespace Jaxon\Plugin\Response\DataBag;

class DataBagContext
{
    /**
     * @var DataBag
     */
    protected $xDataBag;

    /**
     * @var string
     */
    protected $sName;

    /**
     * The constructor
     *
     * @param DataBag $xDataBag
     * @param string $sName
     */
    public function __construct(DataBag $xDataBag, string $sName)
    {
        $this->xDataBag = $xDataBag;
        $this->sName = $sName;
    }

    /**
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return void
     */
    public function set(string $sKey, $xValue)
    {
        $this->xDataBag->set($this->sName, $sKey, $xValue);
    }

    /**
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return mixed
     */
    public function get(string $sKey, $xValue = null)
    {
        return $this->xDataBag->get($this->sName, $sKey, $xValue);
    }
}
