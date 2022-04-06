<?php

namespace Jaxon\Plugin\Response\JQuery\Call;

class AttrGet
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
     * @param string $sAttrName    The attribute name
     */
    public function __construct(string $sAttrName)
    {
        $this->sAttrName = $sAttrName;
    }

    /**
     * Returns a string representation of the script output (javascript) from this call
     *
     * @return string
     */
    public function getScript(): string
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
}
