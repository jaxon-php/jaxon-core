<?php

/**
 * Element.php - A jQuery selector
 *
 * This class is used to create client side requests to the Jaxon functions and callable objects.
 *
 * When inserted into a Jaxon response, an Element object must be converted to the corresponding jQuery code.
 * Therefore, the Element class implements the JsonSerializable interface.
 *
 * When used as a parameter of a Jaxon call, the Element must be converted to Jaxon request parameter.
 * Therefore, the Element class also implements the Jaxon\Request\Interfaces\Parameter interface.
 *
 * @package jaxon-jquery
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-jquery
 */

namespace Jaxon\JQuery\Dom;

use JsonSerializable;
use Jaxon\Jaxon;
use Jaxon\Request\JsCall;
use Jaxon\Request\Interfaces\Parameter;

class Element implements JsonSerializable, Parameter
{
    /**
     * The jQuery selector
     *
     * @var string
     */
    protected $sSelector;

    /**
     * The actions to be applied on the selected element
     *
     * @var array
     */
    protected $aCalls;

    /**
     * The constructor.
     *
     * @param string        $sSelector            The jQuery selector
     * @param string        $sContext             A context associated to the selector
     */
    public function __construct($sSelector = '', $sContext = '')
    {
        $sSelector = trim($sSelector, " \t");
        $sContext = trim($sContext, " \t");
        $this->aCalls = array();

        $jQueryNs = jaxon()->getOption('core.jquery.no_conflict', false) ? 'jQuery' : '$';
        if(!$sSelector)
        {
            $this->sSelector = "$jQueryNs(this)"; // If an empty selector is given, use javascript "this" instead
        }
        elseif(($sContext))
        {
            $this->sSelector = "$jQueryNs('" . $sSelector . "', $jQueryNs('" . $sContext . "'))";
        }
        else
        {
            $this->sSelector = "$jQueryNs('" . $sSelector . "')";
        }
    }

    /**
     * Add a call to a jQuery method on the selected elements
     *
     * @return Element
     */
    public function __call($sMethod, $aArguments)
    {
        // Push the action into the array
        $this->aCalls[] = new Call\Method($sMethod, $aArguments);
        // Return $this so the calls can be chained
        return $this;
    }

    /**
     * Get the value of an attribute on the first selected element
     *
     * @return Element
     */
    public function __get($sAttribute)
    {
        // Push the action into the array
        $this->aCalls[] = new Call\AttrGet($sAttribute);
        // Return $this so the calls can be chained
        return $this;
    }

    /**
     * Set the value of an attribute on the first selected element
     *
     * @return Element
     */
    public function __set($sAttribute, $xValue)
    {
        // Push the action into the array
        $this->aCalls[] = new Call\AttrSet($sAttribute, $xValue);
        // Return null because no other call is allowed after a set
        return null;
    }

    /**
     * Generate the jQuery call.
     *
     * @return string
     */
    public function getScript()
    {
        if(count($this->aCalls) == 0)
        {
            return $this->sSelector;
        }
        return $this->sSelector . '.' . implode('.', $this->aCalls);
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
    public function jsonSerialize()
    {
        return $this->getScript();
    }
}
