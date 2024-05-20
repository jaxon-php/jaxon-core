<?php

/**
 * Selector.php - A jQuery selector
 *
 * This class is used to create client side requests to the Jaxon functions and callable objects.
 *
 * When inserted into a Jaxon response, a Selector object must be converted to the corresponding jQuery code.
 * Therefore, the Selector class implements the JsonSerializable interface.
 *
 * When used as a parameter of a Jaxon call, the Selector must be converted to Jaxon js call parameter.
 * Therefore, the Selector class also implements the Jaxon\JsCall\ParameterInterface interface.
 *
 * @package jaxon-jquery
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-jquery
 */

namespace Jaxon\JsCall;

use function implode;
use function is_a;
use function trim;

class Selector extends AbstractCall
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
     * The constructor.
     *
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     */
    public function __construct(string $sPath, $xContext)
    {
        $this->sPath = trim($sPath, " \t");
        $this->xContext = $xContext;
    }

    /**
     * Add a call to a jQuery method on the selected elements
     *
     * @param string  $sMethod
     * @param array  $aArguments
     *
     * @return Selector
     */
    public function __call(string $sMethod, array $aArguments)
    {
        // Append the action into the array
        $this->aCalls[] = new Selector\Method($sMethod, $aArguments);
        return $this;
    }

    /**
     * Get the value of an attribute on the first selected element
     *
     * @param string  $sAttribute
     *
     * @return Selector
     */
    public function __get(string $sAttribute)
    {
        // Append the action into the array
        $this->aCalls[] = new Selector\AttrGet($sAttribute);
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
        $this->aCalls[] = new Selector\AttrSet($sAttribute, $xValue);
        return $this;
    }

    /**
     * Set an event handler on the first selected element
     *
     * @param string $sName
     * @param AbstractCall $xHandler
     *
     * @return Selector
     */
    public function on(string $sName, AbstractCall $xHandler): Selector
    {
        $this->aCalls[] = new Selector\Event($sName, $xHandler);
        return $this;
    }

    /**
     * Set an "click" event handler on the first selected element
     *
     * @param AbstractCall $xHandler
     *
     * @return Selector
     */
    public function click(AbstractCall $xHandler): Selector
    {
        $this->on('click', $xHandler);
        return $this;
    }

    /**
     * Get the selector js.
     *
     * @return string
     */
    private function getPathAsStr()
    {
        $jQuery = 'jaxon.jq'; // The JQuery selector
        if(!$this->sPath)
        {
            // If an empty selector is given, use the event target instead
            return "$jQuery(e.currentTarget)";
        }
        if(!$this->xContext)
        {
            return "$jQuery('" . $this->sPath . "')";
        }

        $sContext = is_a($this->xContext, self::class) ?
            $this->xContext->getScript() :
            "$jQuery('" . trim("{$this->xContext}") . "')";
        return "$jQuery('{$this->sPath}', $sContext)";
    }

    /**
     * Generate the jQuery call.
     *
     * @return string
     */
    public function __toString(): string
    {
        $sScript = $this->getPathAsStr() . implode('', $this->aCalls);
        return $this->bToInt ? "parseInt($sScript)" : $sScript;
    }

    /**
     * @return array
     */
    private function getPathAsArray()
    {
        $sName = $this->sPath ?? 'this';
        $aCall = ['_type' => 'select', '_name' => $sName];
        if(($this->xContext))
        {
            $aCall['context'] = is_a($this->xContext, self::class) ?
                $this->xContext->jsonSerialize() :$this->xContext;
        }
        return $aCall;
    }

    /**
     * Generate the jQuery call, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $aCalls = [$this->getPathAsArray()];
        foreach($this->aCalls as $xCall)
        {
            $aCalls[] = $xCall->jsonSerialize();
        }
        if($this->bToInt)
        {
            $aCalls[] = $this->toIntCall();
        }
        return ['_type' => $this->getType(), 'calls' => $aCalls];
    }
}
