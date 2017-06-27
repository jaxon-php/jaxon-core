<?php

/**
 * Parameter.php - A parameter of a Jaxon request
 *
 * This class is used to create client side requests to the Jaxon functions and callable objects.
 *
 * @package jaxon-core
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request;

use Jaxon\Jaxon;

class Parameter implements Interfaces\Parameter
{
    /**
     * The request this parameter belongs to.
     *
     * @var Jaxon\Request\Request
     */
    public $xRequest;
    
    /**
     * The parameter type
     *
     * @var string
     */
    protected $sType;

    /**
     * The parameter value
     *
     * @var mixed
     */
    protected $xValue;

    /**
     * The constructor.
     *
     * @param string        $sType                  The parameter type
     * @param mixed         $xValue                 The parameter value
     */
    public function __construct($sType, $xValue)
    {
        $this->sType = $sType;
        $this->xValue = $xValue;
    }

    /**
     * Get the parameter type
     *
     * @return string
     */
    public function getType()
    {
        return $this->sType;
    }

    /**
     * Get the parameter value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->xValue;
    }

    /**
     * Set the parameter value
     *
     * @param mixed         $xValue                 The parameter value
     *
     * @return void
     */
    public function setValue($xValue)
    {
        $this->xValue = $xValue;
    }

    /**
     * Create a Parameter instance using the given value
     *
     * @param mixed         $xValue                 The parameter value
     *
     * @return Parameter
     */
    public static function make($xValue)
    {
        if($xValue instanceof Interfaces\Parameter)
        {
            return $xValue;
        }
        elseif(is_numeric($xValue))
        {
            return new Parameter(Jaxon::NUMERIC_VALUE, $xValue);
        }
        elseif(is_string($xValue))
        {
            return new Parameter(Jaxon::QUOTED_VALUE, $xValue);
        }
        elseif(is_bool($xValue))
        {
            return new Parameter(Jaxon::BOOL_VALUE, $xValue);
        }
        else // if(is_array($xValue) || is_object($xValue))
        {
            return new Parameter(Jaxon::JS_VALUE, $xValue);
        }
    }

    /**
     * Generate the javascript code.
     *
     * @return string
     */
    public function getScript()
    {
        $sJsCode = '';
        $sQuoteCharacter = "'";
        switch($this->sType)
        {
        case Jaxon::FORM_VALUES:
            $sFormID = (string)$this->xValue;
            $sJsCode = "jaxon.getFormValues(" . $sQuoteCharacter . $sFormID . $sQuoteCharacter . ")";
            break;
        case Jaxon::INPUT_VALUE:
            $sInputID = (string)$this->xValue;
            $sJsCode = "jaxon.$("  . $sQuoteCharacter . $sInputID . $sQuoteCharacter  . ").value";
            break;
        case Jaxon::CHECKED_VALUE:
            $sCheckedID = (string)$this->xValue;
            $sJsCode = "jaxon.$("  . $sQuoteCharacter . $sCheckedID  . $sQuoteCharacter . ").checked";
            break;
        case Jaxon::ELEMENT_INNERHTML:
            $sElementID = (string)$this->xValue;
            $sJsCode = "jaxon.$(" . $sQuoteCharacter . $sElementID . $sQuoteCharacter . ").innerHTML";
            break;
        case Jaxon::QUOTED_VALUE:
            $sJsCode = $sQuoteCharacter . addslashes($this->xValue) . $sQuoteCharacter;
            break;
        case Jaxon::BOOL_VALUE:
            $sJsCode = ($this->xValue) ? 'true' : 'false';
            break;
        case Jaxon::PAGE_NUMBER:
            $sJsCode = (string)$this->xValue;
            break;
        case Jaxon::NUMERIC_VALUE:
            $sJsCode = (string)$this->xValue;
            break;
        case Jaxon::JS_VALUE:
            if(is_array($this->xValue) || is_object($this->xValue))
            {
                // Unable to use double quotes here because they cannot be handled on client side.
                // So we are using simple quotes even if the Json standard recommends double quotes.
                $sJsCode = str_replace(['"'], ["'"], json_encode($this->xValue, JSON_HEX_APOS | JSON_HEX_QUOT));
            }
            else
            {
                $sJsCode = (string)$this->xValue;
            }
            break;
        }
        return $sJsCode;
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
