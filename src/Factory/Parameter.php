<?php

/**
 * Factory.php - Jaxon Request Factory
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

namespace Jaxon\Factory;

use Jaxon\Jaxon;
use Jaxon\Request\Parameter as RequestParameter;

class Parameter
{
    /**
     * Make a parameter of type Jaxon::FORM_VALUES
     *
     * @param string        $sFormId                The id of the HTML form
     *
     * @return RequestParameter
     */
    public function form($sFormId)
    {
        return new RequestParameter(Jaxon::FORM_VALUES, $sFormId);
    }

    /**
     * Make a parameter of type Jaxon::INPUT_VALUE
     *
     * @param string $sInputId the id of the HTML input element
     *
     * @return RequestParameter
     */
    public function input($sInputId)
    {
        return new RequestParameter(Jaxon::INPUT_VALUE, $sInputId);
    }

    /**
     * Make a parameter of type Jaxon::CHECKED_VALUE
     *
     * @param string $sInputId the name of the HTML form element
     *
     * @return RequestParameter
     */
    public function checked($sInputId)
    {
        return new RequestParameter(Jaxon::CHECKED_VALUE, $sInputId);
    }

    /**
     * Make a parameter of type Jaxon::CHECKED_VALUE
     *
     * @param string $sInputId the name of the HTML form element
     *
     * @return RequestParameter
     */
    public function select($sInputId)
    {
        return self::input($sInputId);
    }

    /**
     * Make a parameter of type Jaxon::ELEMENT_INNERHTML
     *
     * @param string $sElementId the id of the HTML element
     *
     * @return RequestParameter
     */
    public function html($sElementId)
    {
        return new RequestParameter(Jaxon::ELEMENT_INNERHTML, $sElementId);
    }

    /**
     * Make a parameter of type Jaxon::QUOTED_VALUE
     *
     * @param string $sValue the value of the parameter
     *
     * @return RequestParameter
     */
    public function string($sValue)
    {
        return new RequestParameter(Jaxon::QUOTED_VALUE, $sValue);
    }

    /**
     * Make a parameter of type Jaxon::NUMERIC_VALUE
     *
     * @param numeric $nValue the value of the parameter
     *
     * @return RequestParameter
     */
    public function numeric($nValue)
    {
        return new RequestParameter(Jaxon::NUMERIC_VALUE, intval($nValue));
    }

    /**
     * Make a parameter of type Jaxon::NUMERIC_VALUE
     *
     * @param numeric $nValue the value of the parameter
     *
     * @return RequestParameter
     */
    public function int($nValue)
    {
        return self::numeric($nValue);
    }

    /**
     * Make a parameter of type Jaxon::JS_VALUE
     *
     * @param string $sValue the javascript code of the parameter
     *
     * @return RequestParameter
     */
    public function javascript($sValue)
    {
        return new RequestParameter(Jaxon::JS_VALUE, $sValue);
    }

    /**
     * Make a parameter of type Jaxon::JS_VALUE
     *
     * @param string $sValue the javascript code of the parameter
     *
     * @return RequestParameter
     */
    public function js($sValue)
    {
        return self::javascript($sValue);
    }

    /**
     * Make a parameter of type Jaxon::PAGE_NUMBER
     *
     * @return RequestParameter
     */
    public function page()
    {
        // By default, the value of a parameter of type Jaxon::PAGE_NUMBER is 0.
        return new RequestParameter(Jaxon::PAGE_NUMBER, 0);
    }
}
