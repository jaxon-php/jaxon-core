<?php

namespace Jaxon\Response\Plugin\DataBag;

use function is_array;
use function array_map;

class Context
{
    /**
     * @var Bag
     */
    protected $xBag = '';

    /**
     * @var string
     */
    protected $sBagName;

    /**
     * The constructor
     *
     * @param Bag $xBag
     * @param string $sBagName
     */
    public function __construct(Bag $xBag, $sBagName)
    {
        $this->xBag = $xBag;
        $this->sBagName = $sBagName;
    }

    /**
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return void
     */
    public function set($sKey, $xValue)
    {
        $this->xBag->set($this->sBagName, $sKey, $xValue);
    }

    /**
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return mixed
     */
    public function get($sKey, $xValue = null)
    {
        return $this->xBag->get($this->sBagName, $sKey, $xValue);
    }
}
