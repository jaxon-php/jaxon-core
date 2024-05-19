<?php

namespace Jaxon\Js\Selector;

use Jaxon\Js\Parameter;
use JsonSerializable;
use Stringable;

use function array_map;
use function implode;

class Method implements JsonSerializable, Stringable
{
    /**
     * The name of the javascript function
     *
     * @var string
     */
    private $sName;

    /**
     * @var array<ParameterInterface>
     */
    private $aParameters = [];

    /**
     * The constructor.
     *
     * @param string $sName     The method name
     * @param array $aArguments The method arguments
     */
    public function __construct(string $sName, array $aArguments)
    {
        $this->sName = $sName;
        $this->aParameters = array_map(function($xArgument) {
            return Parameter::make($xArgument);
        }, $aArguments);
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            '_type' => 'method',
            '_name' => $this->sName,
            'args' => array_map(function(JsonSerializable $xParam) {
                return $xParam->jsonSerialize();
            }, $this->aParameters),
        ];
    }

    /**
     * Returns a string representation of this call
     *
     * @return string
     */
    public function __toString(): string
    {
        $aParameters = array_map(function(Stringable $xParam) {
            return $xParam->__toString();
        }, $this->aParameters);
        return '.' . $this->sName . '(' . implode(', ', $aParameters) . ')';
    }
}
