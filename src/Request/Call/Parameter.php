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

namespace Jaxon\Request\Call;

use function is_array;
use function is_bool;
use function is_numeric;
use function is_object;
use function is_string;

class Parameter implements ParameterInterface
{
    /*
     * Request parameters
     */
    // Specifies that the parameter will consist of an array of form values.
    const FORM_VALUES = 'FormValues';
    // Specifies that the parameter will contain the value of an input control.
    const INPUT_VALUE = 'InputValue';
    // Specifies that the parameter will consist of a bool value of a checkbox.
    const CHECKED_VALUE = 'CheckedValue';
    // Specifies that the parameter value will be the innerHTML value of the element.
    const ELEMENT_INNERHTML = 'ElementInnerHTML';
    // Specifies that the parameter will be a quoted value (string).
    const QUOTED_VALUE = 'QuotedValue';
    // Specifies that the parameter will be a bool value (true or false).
    const BOOL_VALUE = 'BoolValue';
    // Specifies that the parameter will be a numeric, non-quoted value.
    const NUMERIC_VALUE = 'NumericValue';
    // Specifies that the parameter will be a non-quoted value
    // (evaluated by the browsers javascript engine at run time).
    const JS_VALUE = 'UnquotedValue';
    // Specifies that the parameter is a call to a javascript function
    const JS_CALL = 'JsCall';
    // Specifies that the parameter must be json encoded
    const JSON_VALUE = 'JsonValue';
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
     * Convert the parameter value to integer
     *
     * @var bool
     */
    protected $bToInt = false;

    /**
     * The constructor.
     *
     * @param string $sType    The parameter type
     * @param mixed $xValue    The parameter value
     */
    public function __construct(string $sType, $xValue)
    {
        $this->sType = $sType;
        $this->xValue = $xValue;
    }

    /**
     * Get the parameter type
     *
     * @return string
     */
    public function getType(): string
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
     * @param mixed $xValue    The parameter value
     *
     * @return void
     */
    public function setValue($xValue)
    {
        $this->xValue = $xValue;
    }

    /**
     * @return ParameterInterface
     */
    public function toInt(): ParameterInterface
    {
        $this->bToInt = true;
        return $this;
    }

    /**
     * Create a Parameter instance using the given value
     *
     * @param mixed $xValue    The parameter value
     *
     * @return ParameterInterface
     */
    public static function make($xValue): ParameterInterface
    {
        if($xValue instanceof ParameterInterface)
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
        if($xValue instanceof JsCall)
        {
            return new Parameter(self::JS_CALL, $xValue);
        }
        // if(is_array($xValue) || is_object($xValue))
        {
            return new Parameter(self::JSON_VALUE, $xValue);
        }
    }

    /**
     * Convert to a value to be inserted in an array for json conversion
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        switch($this->getType())
        {
        case self::JS_CALL:
            return $this->getValue()->toArray();
        case self::JS_VALUE:
            return [
                '_type' => 'expr',
                'calls' => [['_type' => 'attr', '_name' => $this->getValue()]],
            ];
        case self::FORM_VALUES:
            return ['_type' => 'form', '_name' => $this->getValue()];
        case self::INPUT_VALUE:
            return ['_type' => 'input', '_name' => $this->getValue()];
        case self::CHECKED_VALUE:
            return ['_type' => 'checked', '_name' => $this->getValue()];
        case self::ELEMENT_INNERHTML:
            return ['_type' => 'html', '_name' => $this->getValue()];
        case self::PAGE_NUMBER:
            return ['_type' => 'page', '_name' => ''];
        case self::QUOTED_VALUE:
        case self::BOOL_VALUE:
        case self::NUMERIC_VALUE:
        case self::JSON_VALUE:
        default:
            // Return the value as is.
            return $this->getValue();
        }
    }
}
