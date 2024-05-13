<?php

namespace Jaxon\Request\Js\Selector;

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
    public function __toString(): string
    {
        return '.' . $this->sAttrName;
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            '_type' => 'attr',
            '_name' => $this->sAttrName,
        ];
    }
}
