<?php

/**
 * DomSelector.php - A jQuery selector
 *
 * This class is used to create client side requests to the Jaxon functions and callable objects.
 *
 * When inserted into a Jaxon response, a DomSelector object must be converted to the corresponding jQuery code.
 * Therefore, the DomSelector class implements the JsonSerializable interface.
 *
 * When used as a parameter of a Jaxon call, the DomSelector must be converted to Jaxon request parameter.
 * Therefore, the DomSelector class also implements the Jaxon\Request\Call\ParameterInterface interface.
 *
 * @package jaxon-jquery
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-jquery
 */

namespace Jaxon\Plugin\Response\JQuery;

use Jaxon\Plugin\Response\JQuery\Call\AttrGet;
use Jaxon\Plugin\Response\JQuery\Call\AttrSet;
use Jaxon\Plugin\Response\JQuery\Call\Method;
use Jaxon\Request\Call\JsCall;
use Jaxon\Request\Call\ParameterInterface;

use function array_merge;
use function count;
use function is_a;
use function trim;

class DomSelector implements ParameterInterface
{
    /**
     * The jQuery selector path
     *
     * @var string
     */
    protected $sPath;

    /**
     * The jQuery selector path
     *
     * @var mixed
     */
    protected $xContext;

    /**
     * The actions to be applied on the selected element
     *
     * @var array
     */
    protected $aCalls;

    /**
     * Convert the selector value to integer
     *
     * @var bool
     */
    protected $bToInt = false;

    /**
     * True if this selector is a callback
     *
     * @var bool|null
     */
    protected $bIsCallback = null;

    /**
     * The constructor.
     *
     * @param string $jQueryNs    The jQuery symbol
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     */
    public function __construct(string $sPath, $xContext)
    {
        $this->sPath = trim($sPath, " \t");
        $this->xContext = $xContext;
        $this->aCalls = [];
    }
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'select';
    }

    /**
     * Add a call to a jQuery method on the selected elements
     *
     * @param string  $sMethod
     * @param array  $aArguments
     *
     * @return DomSelector
     */
    public function __call(string $sMethod, array $aArguments)
    {
        if(count($aArguments) === 1)
        {
            // If the only parameter is a selector, and the first call
            // on that selector is a method, then the selector is a callback.
            $xArgument = $aArguments[0];
            if(is_a($xArgument, self::class) && $xArgument->bIsCallback === null &&
                count($xArgument->aCalls) > 0 && is_a($xArgument->aCalls[0], JsCall::class))
            {
                $xArgument->bIsCallback = true;
            }
        }
        // Push the action into the array
        $this->aCalls[] = new Method($sMethod, $aArguments);
        // Return $this so the calls can be chained
        return $this;
    }

    /**
     * Get the value of an attribute on the first selected element
     *
     * @param string  $sAttribute
     *
     * @return DomSelector
     */
    public function __get(string $sAttribute)
    {
        // Push the action into the array
        $this->aCalls[] = new AttrGet($sAttribute);
        // Return $this so the calls can be chained
        return $this;
    }

    /**
     * Set the value of an attribute on the first selected element
     *
     * @param string $sAttribute
     * @param $xValue
     *
     * @return void
     */
    public function __set(string $sAttribute, $xValue)
    {
        // Push the action into the array
        $this->aCalls[] = new AttrSet($sAttribute, $xValue);
        // No other call is allowed after a set
        // return $this;
    }

    /**
     * Explicitely declare the selector as a callback.
     *
     * @param bool $bIsCallback
     *
     * @return DomSelector
     */
    public function cb(bool $bIsCallback = true): DomSelector
    {
        $this->bIsCallback = $bIsCallback;
        return $this;
    }

    /**
     * @return DomSelector
     */
    public function toInt(): DomSelector
    {
        $this->bToInt = true;
        return $this;
    }

    /**
     * @return array
     */
    private function selectCalls()
    {
        if(!$this->sPath)
        {
            // If an empty selector is given, use the event target instead
            return [['_type' => 'select', '_name' => 'this']];
        }
        if(!$this->xContext)
        {
            return [['_type' => 'select', '_name' => $this->sPath]];
        }
        // Todo: chain the 2 selectors.
        return [
            // ['_type' => 'select', '_name' => $this->xContext],
            ['_type' => 'select', '_name' => $this->sPath],
        ];
    }

    /**
     * @param Method|AttrSet|AttrGet $xParam
     */
    private function makeCallsArray($xParam): array
    {
        if(!is_a($xParam, Method::class))
        {
            // Return an array of array.
            return [$xParam->jsonSerialize()];
        }
        // The param is serialized to an array of arrays.
        $aCalls = $xParam->jsonSerialize();
        // Set the correct type on the first call.
        $aCalls[0]['_type'] = $this->bIsCallback ? 'event' : 'method';
        return $aCalls;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $aCalls = $this->selectCalls();
        foreach($this->aCalls as $xCall)
        {
            $aCalls = array_merge($aCalls, $this->makeCallsArray($xCall));
        }
        if($this->bToInt)
        {
            $aCalls[] = [
                '_type' => 'func',
                '_name' => 'jaxon.utils.string.toInt',
                'params' => [
                    [ '_type' => '_', '_name' => 'this' ],
                ],
            ];
        }
        return $aCalls;
    }

    /**
     * Generate the jQuery call, when converting the response into json.
     *
     * This is a method of the JsonSerializable interface.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
