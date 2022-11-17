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
use Jaxon\Request\Call\ParameterInterface;

use JsonSerializable;

use function count;
use function implode;
use function trim;

class DomSelector implements JsonSerializable, ParameterInterface
{
    /**
     * The jQuery selector path
     *
     * @var string
     */
    protected $sPath;

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
     * The constructor.
     *
     * @param string $jQueryNs    The jQuery symbol
     * @param string $sPath    The jQuery selector path
     * @param string $sContext    A context associated to the selector
     */
    public function __construct(string $jQueryNs, string $sPath, string $sContext)
    {
        $sPath = trim($sPath, " \t");
        $sContext = trim($sContext, " \t");
        $this->aCalls = [];

        if(!$sPath)
        {
            $this->sPath = "$jQueryNs(this)"; // If an empty selector is given, use javascript "this" instead
        }
        elseif(($sContext))
        {
            $this->sPath = "$jQueryNs('" . $sPath . "', $jQueryNs('" . $sContext . "'))";
        }
        else
        {
            $this->sPath = "$jQueryNs('" . $sPath . "')";
        }
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
     * @return DomSelector
     */
    public function toInt(): DomSelector
    {
        $this->bToInt = true;
        return $this;
    }

    /**
     * Generate the jQuery call.
     *
     * @return string
     */
    public function getScript(): string
    {
        $sScript = $this->sPath;
        if(count($this->aCalls) > 0)
        {
            $sScript .= '.' . implode('.', $this->aCalls);
        }
        return $this->bToInt ? "parseInt($sScript)" : $sScript;
    }

    /**
     * Magic function to generate the jQuery call.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getScript();
    }

    /**
     * Generate the jQuery call, when converting the response into json.
     *
     * This is a method of the JsonSerializable interface.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->getScript();
    }
}
