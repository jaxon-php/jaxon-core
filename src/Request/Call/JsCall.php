<?php

/**
 * JsCall.php - A javascript function call, with its parameters.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Call;

use JsonSerializable;

use function array_map;

class JsCall implements JsonSerializable
{
    /**
     * The name of the javascript function
     *
     * @var string
     */
    private $sFunction;

    /**
     * @var string
     */
    private $sQuoteCharacter = "'";

    /**
     * @var array<ParameterInterface>
     */
    protected $aParameters = [];

    /**
     * Convert the parameter value to integer
     *
     * @var bool
     */
    protected $bToInt = false;

    /**
     * The constructor.
     *
     * @param string $sFunction    The javascript function
     */
    public function __construct(string $sFunction)
    {
        $this->sFunction = $sFunction;
    }

    /**
     * @return JsCall
     */
    public function toInt(): JsCall
    {
        $this->bToInt = true;
        return $this;
    }

    /**
     * Clear the parameter list associated with this request
     *
     * @return JsCall
     */
    public function clearParameters(): JsCall
    {
        $this->aParameters = [];
        return $this;
    }

    /**
     * Set the value of the parameter at the given position
     *
     * @param ParameterInterface $xParameter    The value to be used
     *
     * @return JsCall
     */
    public function pushParameter(ParameterInterface $xParameter): JsCall
    {
        $this->aParameters[] = $xParameter;
        return $this;
    }

    /**
     * Add a parameter value to the parameter list for this request
     *
     * @param string $sType    The type of the value to be used
     * @param string $sValue    The value to be used
     *
     * Types should be one of the following <Parameter::FORM_VALUES>, <Parameter::QUOTED_VALUE>, <Parameter::NUMERIC_VALUE>,
     * <Parameter::JS_VALUE>, <Parameter::INPUT_VALUE>, <Parameter::CHECKED_VALUE>, <Parameter::PAGE_NUMBER>.
     * The value should be as follows:
     * - <Parameter::FORM_VALUES> - Use the ID of the form you want to process.
     * - <Parameter::QUOTED_VALUE> - The string data to be passed.
     * - <Parameter::JS_VALUE> - A string containing valid javascript
     *   (either a javascript variable name that will be in scope at the time of the call or
     *   a javascript function call whose return value will become the parameter).
     *
     * @return JsCall
     */
    public function addParameter(string $sType, string $sValue): JsCall
    {
        $this->pushParameter(new Parameter($sType, $sValue));
        return $this;
    }

    /**
     * Add a set of parameters to this request
     *
     * @param array $aParameters    The parameters
     *
     * @return JsCall
     */
    public function addParameters(array $aParameters): JsCall
    {
        foreach($aParameters as $xParameter)
        {
            $this->pushParameter(Parameter::make($xParameter));
        }
        return $this;
    }

    /**
     * Convert this call to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $aCalls = [[
            '_type' => 'func',
            '_name' => $this->sFunction,
            'params' => array_map(function(Parameter $xParam) {
                return $xParam->jsonSerialize();
            }, $this->aParameters),
        ]];
        if($this->bToInt)
        {
            $aCalls[] = [
                '_type' => 'func',
                '_name' => 'jaxon.utils.string.toInt',
                'params' => [[ '_type' => '_', '_name' => 'this' ]],
            ];
        }
        return [
            '_type' => 'expr',
            'calls' => $aCalls,
        ];
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * This is a method of the JsonSerializable interface.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Returns a string representation of the script output (javascript) from this request object
     *
     * @return string
     */
    public function __toString()
    {
        $aParameters = array_map(function(Parameter $xParam) {
            return $xParam->getScript();
        }, $this->aParameters);
        $sScript = $this->sFunction . '(' . implode(', ', $aParameters) . ')';
        return $this->bToInt ? "parseInt($sScript)" : $sScript;
    }
}
