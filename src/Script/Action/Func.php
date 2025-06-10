<?php

namespace Jaxon\Script\Action;

use JsonSerializable;
use Stringable;

use function array_map;
use function implode;

class Func implements ParameterInterface
{
    /**
     * The name of the javascript function
     *
     * @var string
     */
    private $sName;

    /**
     * The name of the javascript function
     *
     * @var string
     */
    private $sType;

    /**
     * @var array<ParameterInterface>
     */
    private $aArguments = [];

    /**
     * The constructor.
     *
     * @param string $sName     The method name
     * @param array $aArguments The method arguments
     */
    public function __construct(string $sName, array $aArguments)
    {
        $this->sType = 'func';
        $this->sName = $sName;
        $this->aArguments = array_map(function($xArgument) {
            return Parameter::make($xArgument);
        }, $aArguments);
    }

    /**
     * Get the type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->sType;
    }

    /**
     * Check if the request has a parameter of type Parameter::PAGE_NUMBER
     *
     * @return bool
     */
    private function hasPageNumber():  bool
    {
        foreach($this->aArguments as $xArgument)
        {
            if($xArgument->getType() === Parameter::PAGE_NUMBER)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Add the page number to the function arguments.
     *
     * @return self
     */
    public function withPage(): self
    {
        if(!$this->hasPageNumber())
        {
            $this->aArguments[] = new Parameter(Parameter::PAGE_NUMBER, 0);
        }
        return $this;
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            '_type' => $this->getType(),
            '_name' => $this->sName,
            'args' => array_map(function(JsonSerializable $xParam) {
                return $xParam->jsonSerialize();
            }, $this->aArguments),
        ];
    }

    /**
     * Returns a string representation of this call
     *
     * @return string
     */
    public function __toString(): string
    {
        $aArguments = array_map(function(Stringable $xParam) {
            return $xParam->__toString();
        }, $this->aArguments);
        return $this->sName . '(' . implode(', ', $aArguments) . ')';
    }
}
