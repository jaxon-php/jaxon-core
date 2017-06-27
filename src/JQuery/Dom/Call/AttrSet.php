<?php

namespace Jaxon\JQuery\Dom\Call;

use JsonSerializable;

class AttrSet implements JsonSerializable
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
     * @param string        $sAttrName            The attribute name
     * @param mixed         $xAttrValue           The attribute value
     */
    public function __construct($sAttrName, $xAttrValue)
    {
        $this->sAttrName = (string)$sAttrName;
        $this->xAttrValue = (string)$xAttrValue;
    }

    /**
     * Returns a string representation of this call
     *
     * @return string
     */
    public function getScript()
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

    /**
     * Convert this call to string, when converting the response into json.
     *
     * This is a method of the JsonSerializable interface.
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->getScript();
    }
}
