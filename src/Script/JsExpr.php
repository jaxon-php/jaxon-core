<?php

/**
 * JsExpr.php
 *
 * An expression to be formatted in json, that represents a call to a js function or selector.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Script;

use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\Script\Action\Attr;
use Jaxon\Script\Action\Event;
use Jaxon\Script\Action\Func;
use Jaxon\Script\Action\TypedValue;
use JsonSerializable;
use Stringable;

use function array_map;
use function is_a;
use function is_array;
use function json_encode;

class JsExpr extends TypedValue implements Stringable
{
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
    protected $aAlert = [];

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
     * @var DialogCommand
     */
    private static DialogCommand $xDialogCommand;

    /**
     * The constructor
     */
    public function __construct(...$aCalls)
    {
        $this->aCalls = $aCalls;
    }

    /**
     * Set the dialog command
     *
     * @param DialogCommand $xDialogCommand
     *
     * @return void
     */
    public static function setDialogCommand(DialogCommand $xDialogCommand): void
    {
        self::$xDialogCommand = $xDialogCommand;
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
     * @return self
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
     * @param string $sMode    The event mode: 'jq' or 'js'
     * @param string $sName
     * @param JsExpr $xHandler
     *
     * @return self
     */
    public function event(string $sMode, string $sName, JsExpr $xHandler): self
    {
        $this->aCalls[] = new Event($sMode, $sName, $xHandler);
        return $this;
    }

    /**
     * Show a message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     * @param array $aArgs      The message arguments
     *
     * @return self
     */
    public function elseShow(string $sMessage, ...$aArgs): self
    {
        $this->aAlert = self::$xDialogCommand->warning($sMessage, $aArgs);
        return $this;
    }

    /**
     * Show an information message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     * @param array $aArgs      The message arguments
     *
     * @return self
     */
    public function elseInfo(string $sMessage, ...$aArgs): self
    {
        $this->aAlert = self::$xDialogCommand->info($sMessage, $aArgs);
        return $this;
    }

    /**
     * Show a success message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     * @param array $aArgs      The message arguments
     *
     * @return self
     */
    public function elseSuccess(string $sMessage, ...$aArgs): self
    {
        $this->aAlert = self::$xDialogCommand->success($sMessage, $aArgs);
        return $this;
    }

    /**
     * Show a warning message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     * @param array $aArgs      The message arguments
     *
     * @return self
     */
    public function elseWarning(string $sMessage, ...$aArgs): self
    {
        $this->aAlert = self::$xDialogCommand->warning($sMessage, $aArgs);
        return $this;
    }

    /**
     * Show an error message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     * @param array $aArgs      The message arguments
     *
     * @return self
     */
    public function elseError(string $sMessage, ...$aArgs): self
    {
        $this->aAlert = self::$xDialogCommand->error($sMessage, $aArgs);
        return $this;
    }

    /**
     * Add a confirmation question to the request
     *
     * @param string $sQuestion The question to ask
     * @param array $aArgs      The question arguments
     *
     * @return self
     */
    public function confirm(string $sQuestion, ...$aArgs): self
    {
        $this->aConfirm = self::$xDialogCommand->confirm($sQuestion, $aArgs);
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
        $this->aCondition = ['eq', TypedValue::make($xValue1), TypedValue::make($xValue2)];
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
        $this->aCondition = ['teq', TypedValue::make($xValue1), TypedValue::make($xValue2)];
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
        $this->aCondition = ['ne', TypedValue::make($xValue1), TypedValue::make($xValue2)];
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
        $this->aCondition = ['nte', TypedValue::make($xValue1), TypedValue::make($xValue2)];
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
        $this->aCondition = ['gt', TypedValue::make($xValue1), TypedValue::make($xValue2)];
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
        $this->aCondition = ['ge', TypedValue::make($xValue1), TypedValue::make($xValue2)];
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
        $this->aCondition = ['lt', TypedValue::make($xValue1), TypedValue::make($xValue2)];
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
        $this->aCondition = ['le', TypedValue::make($xValue1), TypedValue::make($xValue2)];
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
        $this->aCalls[] = [
            '_type' => 'func',
            '_name' => 'toInt',
        ];
        return $this;
    }

    /**
     * @return self
     */
    public function trim(): self
    {
        $this->aCalls[] = [
            '_type' => 'func',
            '_name' => 'trim',
        ];
        return $this;
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
        $aJsExpr = [
            '_type' => $this->getType(),
            'calls' => array_map(fn(JsonSerializable|array $xCall) =>
                is_array($xCall) ? $xCall : $xCall->jsonSerialize(), $this->aCalls),
        ];
        if(($this->aConfirm))
        {
            $aJsExpr['confirm'] = $this->aConfirm;
        }
        if(($this->aCondition))
        {
            $aJsExpr['condition'] = $this->aCondition;
        }
        if(($this->aAlert))
        {
            $aJsExpr['alert'] = $this->aAlert;
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
}
