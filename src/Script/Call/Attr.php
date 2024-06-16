<?php

namespace Jaxon\Script\Call;

use Jaxon\Script\Parameter;
use Jaxon\Script\ParameterInterface;

class Attr implements ParameterInterface
{
    /**
     * The attribute value
     *
     * @var array
     */
    private $aValue;

    /**
     * Get the parameter type
     *
     * @return string
     */
    public function getType(): string
    {
        return 'attr';
    }

    /**
     * Create an abject to get the value of an attribute
     *
     * @param string $sAttrName    The attribute name
     *
     * @return Attr
     */
    static public function get(string $sAttrName): Attr
    {
        $xAttr = new Attr();
        $xAttr->aValue = [
            '_type' => 'attr',
            '_name' => $sAttrName,
        ];
        return $xAttr;
    }

    /**
     * Create an abject to set the value of an attribute
     *
     * @param string $sAttrName    The attribute name
     * @param mixed $xAttrValue    The attribute value
     */
    static public function set(string $sAttrName, $xAttrValue = null)
    {
        $xAttr = new Attr();
        $xAttr->aValue = [
            '_type' => 'attr',
            '_name' => $sAttrName,
            'value' => Parameter::make($xAttrValue),
        ];
        return $xAttr;
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        if(isset($this->aValue['value']))
        {
            $this->aValue['value'] = $this->aValue['value']->jsonSerialize();
        }
        return $this->aValue;
    }

    /**
     * Returns a string representation of this call
     *
     * @return string
     */
    public function __toString(): string
    {
        return !isset($this->aValue['value']) ? $this->aValue['_name'] :
            $this->aValue['_name'] . ' = ' . $this->aValue['value']->__toString();
    }
}
