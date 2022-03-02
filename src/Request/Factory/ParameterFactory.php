<?php

/**
 * RequestFactory.php - Jaxon Request Factory
 *
 * Create Jaxon client side requests, which will generate the client script necessary
 * to invoke a jaxon request from the browser to registered objects.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Factory;

// Extends Parameter for compatibility with older versions (see function rq())
class ParameterFactory
{
    /**
     * Make a parameter of type Parameter::FORM_VALUES
     *
     * @param string        $sFormId                The id of the HTML form
     *
     * @return Parameter
     */
    public function form(string $sFormId)
    {
        return new Parameter(Parameter::FORM_VALUES, $sFormId);
    }

    /**
     * Make a parameter of type Parameter::INPUT_VALUE
     *
     * @param string $sInputId the id of the HTML input element
     *
     * @return Parameter
     */
    public function input(string $sInputId)
    {
        return new Parameter(Parameter::INPUT_VALUE, $sInputId);
    }

    /**
     * Make a parameter of type Parameter::CHECKED_VALUE
     *
     * @param string $sInputId the name of the HTML form element
     *
     * @return Parameter
     */
    public function checked(string $sInputId)
    {
        return new Parameter(Parameter::CHECKED_VALUE, $sInputId);
    }

    /**
     * Make a parameter of type Parameter::CHECKED_VALUE
     *
     * @param string $sInputId the name of the HTML form element
     *
     * @return Parameter
     */
    public function select(string $sInputId)
    {
        return self::input($sInputId);
    }

    /**
     * Make a parameter of type Parameter::ELEMENT_INNERHTML
     *
     * @param string $sElementId the id of the HTML element
     *
     * @return Parameter
     */
    public function html(string $sElementId)
    {
        return new Parameter(Parameter::ELEMENT_INNERHTML, $sElementId);
    }

    /**
     * Make a parameter of type Parameter::QUOTED_VALUE
     *
     * @param string $sValue the value of the parameter
     *
     * @return Parameter
     */
    public function string(string $sValue)
    {
        return new Parameter(Parameter::QUOTED_VALUE, $sValue);
    }

    /**
     * Make a parameter of type Parameter::NUMERIC_VALUE
     *
     * @param numeric $nValue the value of the parameter
     *
     * @return Parameter
     */
    public function numeric(int $nValue)
    {
        return new Parameter(Parameter::NUMERIC_VALUE, intval($nValue));
    }

    /**
     * Make a parameter of type Parameter::NUMERIC_VALUE
     *
     * @param numeric $nValue the value of the parameter
     *
     * @return Parameter
     */
    public function int(int $nValue)
    {
        return self::numeric($nValue);
    }

    /**
     * Make a parameter of type Parameter::JS_VALUE
     *
     * @param string $sValue the javascript code of the parameter
     *
     * @return Parameter
     */
    public function javascript(string $sValue)
    {
        return new Parameter(Parameter::JS_VALUE, $sValue);
    }

    /**
     * Make a parameter of type Parameter::JS_VALUE
     *
     * @param string $sValue the javascript code of the parameter
     *
     * @return Parameter
     */
    public function js(string $sValue)
    {
        return self::javascript($sValue);
    }

    /**
     * Make a parameter of type Parameter::PAGE_NUMBER
     *
     * @return Parameter
     */
    public function page()
    {
        // By default, the value of a parameter of type Parameter::PAGE_NUMBER is 0.
        return new Parameter(Parameter::PAGE_NUMBER, 0);
    }
}
