<?php

namespace Jaxon\Script\Action;

class Attr extends TypedValue
{
    /**
     * The constructor
     *
     * @param array $aValue
     */
    public function __construct(private array $aValue)
    {}

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->aValue['_type'] ?? '_';
    }

    /**
     * Create an abject to get the value of an attribute
     *
     * @param string $sAttrName    The attribute name
     *
     * @return Attr
     */
    public static function get(string $sAttrName): Attr
    {
        return new Attr([
            '_type' => 'attr',
            '_name' => $sAttrName,
        ]);
    }

    /**
     * Create an abject to set the value of an attribute
     *
     * @param string $sAttrName    The attribute name
     * @param mixed $xAttrValue    The attribute value
     *
     * @return Attr
     */
    public static function set(string $sAttrName, $xAttrValue): Attr
    {
        return new Attr([
            '_type' => 'attr',
            '_name' => $sAttrName,
            'value' => TypedValue::make($xAttrValue)->jsonSerialize(),
        ]);
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->aValue;
    }
}
