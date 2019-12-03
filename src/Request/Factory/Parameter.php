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

namespace Jaxon\Request\Factory;

class Parameter implements Contracts\Parameter
{
    /*
     * Request parameters
     */
    // Specifies that the parameter will consist of an array of form values.
    const FORM_VALUES = 'FormValues';
    // Specifies that the parameter will contain the value of an input control.
    const INPUT_VALUE = 'InputValue';
    // Specifies that the parameter will consist of a boolean value of a checkbox.
    const CHECKED_VALUE = 'CheckedValue';
    // Specifies that the parameter value will be the innerHTML value of the element.
    const ELEMENT_INNERHTML = 'ElementInnerHTML';
    // Specifies that the parameter will be a quoted value (string).
    const QUOTED_VALUE = 'QuotedValue';
    // Specifies that the parameter will be a boolean value (true or false).
    const BOOL_VALUE = 'BoolValue';
    // Specifies that the parameter will be a numeric, non-quoted value.
    const NUMERIC_VALUE = 'NumericValue';
    // Specifies that the parameter will be a non-quoted value
    // (evaluated by the browsers javascript engine at run time).
    const JS_VALUE = 'UnquotedValue';
    // Specifies that the parameter will be an integer used to generate pagination links.
    const PAGE_NUMBER = 'PageNumber';

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
        if($xValue instanceof Contracts\Parameter)
        {
            return $xValue;
        }
        if(is_numeric($xValue))
        {
            return new Parameter(self::NUMERIC_VALUE, $xValue);
        }
        if(is_string($xValue))
        {
            return new Parameter(self::QUOTED_VALUE, $xValue);
        }
        if(is_bool($xValue))
        {
            return new Parameter(self::BOOL_VALUE, $xValue);
        }
        // if(is_array($xValue) || is_object($xValue))
        {
            return new Parameter(self::JS_VALUE, $xValue);
        }
    }

    /**
     * Add quotes to a given value
     *
     * @param string    $xValue     The value to be quoted
     *
     * @return string
     */
    private function getQuotedValue($xValue)
    {
        $sQuoteCharacter = "'";
        return $sQuoteCharacter . $xValue . $sQuoteCharacter;
    }

    /**
     * Get a js call to Jaxon with a single parameter
     *
     * @param string    $sFunction      The function name
     * @param string    $sParameter     The function parameter
     *
     * @return string
     */
    private function getJsCall($sFunction, $sParameter)
    {
        return 'jaxon.' . $sFunction . '(' . $this->getQuotedValue($sParameter) . ')';
    }

    /**
     * Get the script for an array of form values.
     *
     * @return string
     */
    protected function getFormValuesScript()
    {
        return $this->getJsCall('getFormValues', $this->xValue);
    }

    /**
     * Get the script for an input control.
     *
     * @return string
     */
    protected function getInputValueScript()
    {
        return $this->getJsCall('$', $this->xValue) . '.value';
    }

    /**
     * Get the script for a boolean value of a checkbox.
     *
     * @return string
     */
    protected function getCheckedValueScript()
    {
        return $this->getJsCall('$', $this->xValue) . '.checked';
    }

    /**
     * Get the script for the innerHTML value of the element.
     *
     * @return string
     */
    protected function getElementInnerHTMLScript()
    {
        return $this->getJsCall('$', $this->xValue) . '.innerHTML';
    }

    /**
     * Get the script for a quoted value (string).
     *
     * @return string
     */
    protected function getQuotedValueScript()
    {
        return $this->getQuotedValue(addslashes($this->xValue));
    }

    /**
     * Get the script for a boolean value (true or false).
     *
     * @return string
     */
    protected function getBoolValueScript()
    {
        return ($this->xValue) ? 'true' : 'false';
    }

    /**
     * Get the script for a numeric, non-quoted value.
     *
     * @return string
     */
    protected function getNumericValueScript()
    {
        return (string)$this->xValue;
    }

    /**
     * Get the script for a non-quoted value (evaluated by the browsers javascript engine at run time).
     *
     * @return string
     */
    protected function getUnquotedValueScript()
    {
        if(is_array($this->xValue) || is_object($this->xValue))
        {
            // Unable to use double quotes here because they cannot be handled on client side.
            // So we are using simple quotes even if the Json standard recommends double quotes.
            return str_replace('"', "'", json_encode($this->xValue, JSON_HEX_APOS | JSON_HEX_QUOT));
        }
        return (string)$this->xValue;
    }

    /**
     * Get the script for an integer used to generate pagination links.
     *
     * @return string
     */
    protected function getPageNumberScript()
    {
        return (string)$this->xValue;
    }

    /**
     * Generate the javascript code.
     *
     * @return string
     */
    public function getScript()
    {
        $sMethodName = 'get' . $this->sType . 'Script';
        if(!\method_exists($this, $sMethodName))
        {
            return '';
        }
        return $this->$sMethodName();
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
