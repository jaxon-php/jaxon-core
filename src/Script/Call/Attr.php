<?php

namespace Jaxon\Script\Call;

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
        return $this->aValue['_type'];
    }

    /**
     * Create an abject to get the value of an attribute
     *
     * @param string $sAttrName    The attribute name
     * @param bool $bHasParent
     *
     * @return Attr
     */
    static public function get(string $sAttrName, bool $bHasParent): Attr
    {
        $xAttr = new Attr();
        $xAttr->aValue = [
            '_type' => $bHasParent ? 'attr' : 'gvar',
            '_name' => $sAttrName,
        ];
        return $xAttr;
    }

    /**
     * Create an abject to set the value of an attribute
     *
     * @param string $sAttrName    The attribute name
     * @param mixed $xAttrValue    The attribute value
     * @param bool $bHasParent
     *
     * @return Attr
     */
    static public function set(string $sAttrName, $xAttrValue, bool $bHasParent)
    {
        $xAttr = new Attr();
        $xAttr->aValue = [
            '_type' => $bHasParent ? 'attr' : 'gvar',
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
