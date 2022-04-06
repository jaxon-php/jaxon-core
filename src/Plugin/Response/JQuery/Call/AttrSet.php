<?php

namespace Jaxon\Plugin\Response\JQuery\Call;

use Jaxon\Request\Call\Parameter;

class AttrSet
{
    /**
     * The attribute name
     *
     * @var string
     */
    private $sAttrName;

    /**
     * The attribute value
     *
     * @var mixed
     */
    private $xAttrValue;

    /**
     * The constructor.
     *
     * @param string $sAttrName    The attribute name
     * @param mixed $xAttrValue    The attribute value
     */
    public function __construct(string $sAttrName, $xAttrValue)
    {
        $this->sAttrName = $sAttrName;
        $this->xAttrValue = Parameter::make($xAttrValue);
    }

    /**
     * Returns a string representation of this call
     *
     * @return string
     */
    public function getScript(): string
    {
        return $this->sAttrName . ' = ' . $this->xAttrValue;
    }

    /**
     * Convert this call to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getScript();
    }
}
