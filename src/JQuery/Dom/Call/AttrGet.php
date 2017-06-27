<?php

namespace Jaxon\JQuery\Dom\Call;

use JsonSerializable;

class AttrGet implements JsonSerializable
{
    /**
     * The attribute name
     *
     * @var string
     */
    private $sAttrName;

    /**
     * The constructor.
     *
     * @param string        $sAttrName            The attribute name
     */
    public function __construct($sAttrName)
    {
        $this->sAttrName = (string)$sAttrName;
    }

    /**
     * Returns a string representation of the script output (javascript) from this call
     *
     * @return string
     */
    public function getScript()
    {
        return $this->sAttrName;
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
