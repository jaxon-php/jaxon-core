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
use Jaxon\Plugin\Response\JQuery\Call\Event;
use Jaxon\Plugin\Response\JQuery\Call\Method;
use Jaxon\Request\Call\Call;
use Jaxon\Request\Call\ParameterInterface;

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
     * The jQuery selector context
     *
     * @var mixed
     */
    protected $xContext;

    /**
     * The actions to be applied on the selected element
     *
     * @var array
     */
    protected $aCalls = [];

    /**
     * Convert the selector value to integer
     *
     * @var bool
     */
    protected $bToInt = false;

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
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'selector';
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
        // Append the action into the array
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
        // Append the action into the array
        $this->aCalls[] = new AttrGet($sAttribute);
        // Return $this so the calls can be chained
        return $this;
    }

    /**
     * Set the value of an attribute on the first selected element
     *
     * @param string $sAttribute
     * @param mixed $xValue
     *
     * @return void
     */
    public function __set(string $sAttribute, $xValue)
    {
        // Append the action into the array
        $this->aCalls[] = new AttrSet($sAttribute, $xValue);
        // No other call is allowed after a set
        // return $this;
    }

    /**
     * Set an event handler on the first selected element
     *
     * @param string $sName
     * @param Call $xHandler
     *
     * @return void
     */
    public function on(string $sName, Call $xHandler)
    {
        $this->aCalls[] = new Event($sName, $xHandler);
    }

    /**
     * Set an "click" event handler on the first selected element
     *
     * @param Call $xHandler
     *
     * @return void
     */
    public function click(Call $xHandler)
    {
        $this->aCalls[] = new Event('click', $xHandler);
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
    private function selector()
    {
        $sName = $this->sPath ?? 'this';
        $aCall = ['_type' => 'select', '_name' => $sName];
        if(($this->xContext))
        {
            $aCall['context'] = $this->xContext;
        }
        return $aCall;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $aCalls = [$this->selector()];
        foreach($this->aCalls as $xCall)
        {
            $aCalls[] = $xCall->jsonSerialize();
        }
        if($this->bToInt)
        {
            $aCalls[] = [
                '_type' => 'func',
                '_name' => 'toInt',
                'args' => [[ '_type' => '_', '_name' => 'this' ]],
            ];
        }
        return ['_type' => 'expr', 'calls' => $aCalls];
    }

    /**
     * Generate the jQuery call, when converting the response into json.
     * This is a method of the JsonSerializable interface.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
