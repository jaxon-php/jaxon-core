<?php

/**
 * JsExpr.php
 *
 * Base class for js call factory and selector classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\JsCall;

use Jaxon\App\Dialog\DialogManager;
use Jaxon\JsCall\Js\Attr;
use Jaxon\JsCall\Js\Event;
use Jaxon\JsCall\Js\Func;
use Jaxon\JsCall\Js\Selector;
use Jaxon\JsCall\ParameterInterface;
use Jaxon\JsCall\Parameter;
use JsonSerializable;
use Stringable;

use function array_map;
use function array_shift;
use function func_get_args;
use function implode;
use function is_a;
use function json_encode;

class JsExpr implements ParameterInterface
{
    /**
     * Dialog for confirm questions and messages
     *
     * @var DialogManager
     */
    protected $xDialog;

    /**
     * The actions to be applied on the selected element
     *
     * @var array
     */
    protected $aCalls = [];

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
     * Convert the expression value to int
     *
     * @var bool
     */
    protected $bToInt = false;

    /**
     * @param DialogManager $xDialog
     * @param Selector|null $xSelector
     */
    public function __construct(DialogManager $xDialog, ?Selector $xSelector = null)
    {
        $this->xDialog = $xDialog;
        if($xSelector !== null)
        {
            $this->aCalls[] = $xSelector;
        }
    }

    /**
     * Get the first function in the calls
     *
     * @return Func|null
     */
    public function func(): ?Func
    {
        foreach($this->aCalls as $xCall)
        {
            if(is_a($xCall, Func::class))
            {
                return $xCall;
            }
        }
        return null;
    }

    /**
     * Add a call to a js function on the current object
     *
     * @param string  $sMethod
     * @param array  $aArguments
     *
     * @return self
     */
    public function __call(string $sMethod, array $aArguments): self
    {
        // Append the action into the array
        $this->aCalls[] = new Func($sMethod, $aArguments);
        return $this;
    }

    /**
     * Get the value of an attribute of the current object
     *
     * @param string  $sAttribute
     *
     * @return self
     */
    public function __get(string $sAttribute): self
    {
        // Append the action into the array
        $this->aCalls[] = Attr::get($sAttribute);
        return $this;
    }

    /**
     * Set the value of an attribute of the current object
     *
     * @param string $sAttribute
     * @param mixed $xValue
     *
     * @return void
     */
    public function __set(string $sAttribute, $xValue)
    {
        // Append the action into the array
        $this->aCalls[] = Attr::set($sAttribute, $xValue);
        return $this;
    }

    /**
     * Set an event handler on the selected elements
     *
     * @param string $sName
     * @param JsExpr $xHandler
     *
     * @return self
     */
    public function on(string $sName, JsExpr $xHandler): self
    {
        $this->aCalls[] = new Event($sName, $xHandler);
        return $this;
    }

    /**
     * Set a "click" event handler on the selected elements
     *
     * @param JsExpr $xHandler
     *
     * @return self
     */
    public function click(JsExpr $xHandler): self
    {
        return $this->on('click', $xHandler);
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
     * Show a message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return self
     */
    public function elseShow(string $sMessage): self
    {
        $this->aMessage = $this->xDialog->warning($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show an information message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return self
     */
    public function elseInfo(string $sMessage): self
    {
        $this->aMessage = $this->xDialog->info($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show a success message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return self
     */
    public function elseSuccess(string $sMessage): self
    {
        $this->aMessage = $this->xDialog->success($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show a warning message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return self
     */
    public function elseWarning(string $sMessage): self
    {
        $this->aMessage = $this->xDialog->warning($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show an error message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return self
     */
    public function elseError(string $sMessage): self
    {
        $this->aMessage = $this->xDialog->error($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Add a confirmation question to the request
     *
     * @param string $sQuestion    The question to ask
     *
     * @return self
     */
    public function confirm(string $sQuestion): self
    {
        $this->aConfirm = $this->xDialog->confirm($sQuestion, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Check if a value is equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return self
     */
    public function ifeq($xValue1, $xValue2): self
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
     * @return self
     */
    public function ifteq($xValue1, $xValue2): self
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
     * @return self
     */
    public function ifne($xValue1, $xValue2): self
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
     * @return self
     */
    public function ifnte($xValue1, $xValue2): self
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
     * @return self
     */
    public function ifgt($xValue1, $xValue2): self
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
     * @return self
     */
    public function ifge($xValue1, $xValue2): self
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
     * @return self
     */
    public function iflt($xValue1, $xValue2): self
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
     * @return self
     */
    public function ifle($xValue1, $xValue2): self
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
     * @return self
     */
    public function when($xCondition): self
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
     * @return self
     */
    public function unless($xCondition): self
    {
        return $this->ifeq(false, $xCondition);
    }

    /**
     * @return self
     */
    public function toInt(): self
    {
        $this->bToInt = true;
        return $this;
    }

    /**
     * return array
     */
    protected function toIntCall(): array
    {
        return [
            '_type' => 'method',
            '_name' => 'toInt',
            'args' => [],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'expr';
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $aCalls = array_map(function(JsonSerializable $xCall) {
            return $xCall->jsonSerialize();
        }, $this->aCalls);
        if($this->bToInt)
        {
            $aCalls[] = $this->toIntCall();
        }

        $aJsExpr = ['_type' => $this->getType(), 'calls' => $aCalls];
        if(($this->aConfirm))
        {
            $aJsExpr['question'] = $this->aConfirm;
        }
        if(($this->aCondition))
        {
            $aJsExpr['condition'] = $this->aCondition;
        }
        if(($this->aMessage))
        {
            $aJsExpr['message'] = $this->aMessage;
        }
        return $aJsExpr;
    }

    /**
     * Returns a call to jaxon as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'jaxon.exec(' . json_encode($this->jsonSerialize()) . ')';
    }

    /**
     * Returns the js code of the call
     *
     * @return string
     */
    public function raw(): string
    {
        $sScript = implode('.', array_map(function(Stringable $xParam) {
            return $xParam->__toString();
        }, $this->aCalls));
        return $this->bToInt ? "parseInt($sScript)" : $sScript;
    }
}
