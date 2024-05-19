<?php

namespace Jaxon\Js\Selector;

use Jaxon\Js\Parameter;

use JsonSerializable;
use Stringable;

class AttrSet implements JsonSerializable, Stringable
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
     * @var Parameter
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
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            '_type' => 'attr',
            '_name' => $this->sAttrName,
            'value' => $this->xAttrValue->jsonSerialize(),
        ];
    }

    /**
     * Returns a string representation of this call
     *
     * @return string
     */
    public function __toString(): string
    {
        return '.' . $this->sAttrName . ' = ' . $this->xAttrValue;
    }
}
