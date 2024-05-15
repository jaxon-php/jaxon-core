<?php

/**
 * Call.php - The Jaxon Call
 *
 * This class is used to create js ajax requests to callable classes and functions.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Js;

use Jaxon\App\Dialog\DialogManager;
use JsonSerializable;
use Stringable;

use function array_map;
use function array_shift;
use function func_get_args;
use function implode;
use function json_encode;

class Call implements JsonSerializable, Stringable
{
    /**
     * @var DialogManager
     */
    protected $xDialogManager;

    /**
     * The name of the javascript function
     *
     * @var string
     */
    private $sFunction;

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
     * The arguments of the else() calls
     *
     * @var array
     */
    protected $aMessage = [];

    /**
     * A condition to check before making the call
     *
     * @var array
     */
    protected $aCondition = [];

    /**
     * The arguments of the confirm() call
     *
     * @var array
     */
    protected $aConfirm = [];

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
     * @return Call
     */
    public function toInt(): Call
    {
        $this->bToInt = true;
        return $this;
    }

    /**
     * Clear the parameter list associated with this request
     *
     * @return Call
     */
    public function clearParameters(): Call
    {
        $this->aParameters = [];
        return $this;
    }

    /**
     * Set the value of the parameter at the given position
     *
     * @param ParameterInterface $xParameter    The value to be used
     *
     * @return Call
     */
    public function pushParameter(ParameterInterface $xParameter): Call
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
     * @return Call
     */
    public function addParameter(string $sType, string $sValue): Call
    {
        $this->pushParameter(new Parameter($sType, $sValue));
        return $this;
    }

    /**
     * Add a set of parameters to this request
     *
     * @param array $aParameters    The parameters
     *
     * @return Call
     */
    public function addParameters(array $aParameters): Call
    {
        foreach($aParameters as $xParameter)
        {
            $this->pushParameter(Parameter::make($xParameter));
        }
        return $this;
    }

    /**
     * @param array $aArgs
     *
     * @return array
     */
    private function getArgs(array $aArgs): array
    {
        array_shift($aArgs);
        return $aArgs;
    }

    /**
     * @param DialogManager $xDialogManager
     *
     * @return void
     */
    public function setDialogManager(DialogManager $xDialogManager)
    {
        $this->xDialogManager = $xDialogManager;
    }

    /**
     * Show a message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseShow(string $sMessage): Call
    {
        $this->aMessage = $this->xDialogManager->warning($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show an information message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseInfo(string $sMessage): Call
    {
        $this->aMessage = $this->xDialogManager->info($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show a success message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseSuccess(string $sMessage): Call
    {
        $this->aMessage = $this->xDialogManager->success($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show a warning message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseWarning(string $sMessage): Call
    {
        $this->aMessage = $this->xDialogManager->warning($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show an error message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseError(string $sMessage): Call
    {
        $this->aMessage = $this->xDialogManager->error($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Add a confirmation question to the request
     *
     * @param string $sQuestion    The question to ask
     *
     * @return Call
     */
    public function confirm(string $sQuestion): Call
    {
        $this->aConfirm = $this->xDialogManager->confirm($sQuestion, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Check if a value is equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifeq($xValue1, $xValue2): Call
    {
        $this->aCondition = ['eq', Parameter::make($xValue1), Parameter::make($xValue2)];
        return $this;
    }

    /**
     * Check if a value is equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifteq($xValue1, $xValue2): Call
    {
        $this->aCondition = ['teq', Parameter::make($xValue1), Parameter::make($xValue2)];
        return $this;
    }

    /**
     * Check if a value is not equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifne($xValue1, $xValue2): Call
    {
        $this->aCondition = ['ne', Parameter::make($xValue1), Parameter::make($xValue2)];
        return $this;
    }

    /**
     * Check if a value is not equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifnte($xValue1, $xValue2): Call
    {
        $this->aCondition = ['nte', Parameter::make($xValue1), Parameter::make($xValue2)];
        return $this;
    }

    /**
     * Check if a value is greater than another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifgt($xValue1, $xValue2): Call
    {
        $this->aCondition = ['gt', Parameter::make($xValue1), Parameter::make($xValue2)];
        return $this;
    }

    /**
     * Check if a value is greater or equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifge($xValue1, $xValue2): Call
    {
        $this->aCondition = ['ge', Parameter::make($xValue1), Parameter::make($xValue2)];
        return $this;
    }

    /**
     * Check if a value is lower than another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function iflt($xValue1, $xValue2): Call
    {
        $this->aCondition = ['lt', Parameter::make($xValue1), Parameter::make($xValue2)];
        return $this;
    }

    /**
     * Check if a value is lower or equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifle($xValue1, $xValue2): Call
    {
        $this->aCondition = ['le', Parameter::make($xValue1), Parameter::make($xValue2)];
        return $this;
    }

    /**
     * Add a condition to the request
     *
     * The request is sent only if the condition is true.
     *
     * @param mixed $xCondition    The condition to check
     *
     * @return Call
     */
    public function when($xCondition): Call
    {
        return $this->ifeq(true, $xCondition);
    }

    /**
     * Add a condition to the request
     *
     * The request is sent only if the condition is false.
     *
     * @param mixed $xCondition    The condition to check
     *
     * @return Call
     */
    public function unless($xCondition): Call
    {
        return $this->ifeq(false, $xCondition);
    }

    /**
     * Check if the request has a parameter of type Parameter::PAGE_NUMBER
     *
     * @return ParameterInterface|null
     */
    private function findPageNumber(): ?ParameterInterface
    {
        foreach($this->aParameters as $xParameter)
        {
            if($xParameter->getType() === Parameter::PAGE_NUMBER)
            {
                return $xParameter;
            }
        }
        return null;
    }

    /**
     * Check if the request has a parameter of type Parameter::PAGE_NUMBER
     *
     * @return bool
     */
    public function hasPageNumber(): bool
    {
        return $this->findPageNumber() !== null;
    }

    /**
     * Set a value to the Parameter::PAGE_NUMBER parameter
     *
     * @param integer $nPageNumber    The current page number
     *
     * @return Call
     */
    public function setPageNumber(int $nPageNumber): Call
    {
        /** @var Parameter */
        $xParameter = $this->findPageNumber();
        if($xParameter !== null)
        {
            $xParameter->setValue($nPageNumber);
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
            'args' => array_map(function(JsonSerializable $xParam) {
                return $xParam->jsonSerialize();
            }, $this->aParameters),
        ]];
        if($this->bToInt)
        {
            $aCalls[] = [
                '_type' => 'func',
                '_name' => 'toInt',
                'args' => [[ '_type' => '_', '_name' => 'this' ]],
            ];
        }

        $aCall = ['_type' => 'expr', 'calls' => $aCalls];
        if(($this->aConfirm))
        {
            $aCall['question'] = $this->aConfirm;
        }
        if(($this->aCondition))
        {
            $aCall['condition'] = $this->aCondition;
        }
        if(($this->aMessage))
        {
            $aCall['message'] = $this->aMessage;
        }

        return $aCall;
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Returns a call to jaxon as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'jaxon.exec(' . json_encode($this->toArray()) . ')';
    }

    /**
     * Returns the js code of the call
     *
     * @return string
     */
    public function toJs(): string
    {
        $aParameters = array_map(function(Stringable $xParam) {
            return $xParam->__toString();
        }, $this->aParameters);
        $sScript = $this->sFunction . '(' . implode(', ', $aParameters) . ')';
        return $this->bToInt ? "parseInt($sScript)" : $sScript;
    }
}
