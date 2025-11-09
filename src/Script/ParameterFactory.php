<?php

/**
 * ParameterFactory.php
 *
 * Create parameters for calls to js functions and selectors.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Script;

use Jaxon\Script\Action\HtmlReader;
use Jaxon\Script\Action\TypedValue;

class ParameterFactory
{
    /**
     * Make a parameter of type form values
     *
     * @param string $sFormId    The id of the HTML form
     *
     * @return array
     */
    public function form(string $sFormId): array
    {
        return (new HtmlReader($sFormId))->form();
    }

    /**
     * Make a parameter of type input value
     *
     * @param string $sInputId    the id of the HTML input element
     *
     * @return TypedValue
     */
    public function input(string $sInputId): TypedValue
    {
        return (new HtmlReader($sInputId))->input();
    }

    /**
     * Make a parameter of type checked value
     *
     * @param string $sInputId    the name of the HTML form element
     *
     * @return array
     */
    public function checked(string $sInputId): array
    {
        return (new HtmlReader($sInputId))->checked();
    }

    /**
     * Make a parameter of type select
     *
     * @param string $sInputId    the name of the HTML form element
     *
     * @return TypedValue
     */
    public function select(string $sInputId): TypedValue
    {
        return $this->input($sInputId);
    }

    /**
     * Make a parameter of type inner html
     *
     * @param string $sElementId    the id of the HTML element
     *
     * @return TypedValue
     */
    public function html(string $sElementId): TypedValue
    {
        return (new HtmlReader($sElementId))->html();
    }

    /**
     * Make a parameter of type quoted string
     *
     * @param string $sValue    the value of the parameter
     *
     * @return TypedValue
     */
    public function string(string $sValue): TypedValue
    {
        return TypedValue::make($sValue);
    }

    /**
     * Make a parameter of type numeric
     *
     * @param int $nValue    the value of the parameter
     *
     * @return TypedValue
     */
    public function numeric(int $nValue): TypedValue
    {
        return TypedValue::make($nValue);
    }

    /**
     * Make a parameter of type numeric
     *
     * @param int $nValue    the value of the parameter
     *
     * @return TypedValue
     */
    public function int(int $nValue): TypedValue
    {
        return $this->numeric($nValue);
    }

    /**
     * Make a parameter of type page number
     *
     * @return TypedValue
     */
    public function page(): TypedValue
    {
        return TypedValue::page();
    }
}
