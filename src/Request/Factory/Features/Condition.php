<?php

/**
 * Condition.php - Add conditions to a Jaxon call
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Factory\Features;

use Jaxon\Request\Factory\Request;
use Jaxon\Request\Factory\Parameter;

trait Condition
{
    /**
     * A condition to check before sending this request
     *
     * @var string
     */
    protected $sCondition = null;

    /**
     * Add a condition to the request
     *
     * The request is sent only if the condition is true.
     *
     * @param string        $sCondition               The condition to check
     *
     * @return mixed
     */
    public function when($sCondition)
    {
        $this->sCondition = Parameter::make($sCondition)->getScript();
        return $this;
    }

    /**
     * Add a condition to the request
     *
     * The request is sent only if the condition is false.
     *
     * @param string        $sCondition               The condition to check
     *
     * @return mixed
     */
    public function unless($sCondition)
    {
        $this->sCondition = '!(' . Parameter::make($sCondition)->getScript() . ')';
        return $this;
    }

    /**
     * Check if a value is equal to another before sending the request
     *
     * @param string        $sValue1                  The first value to compare
     * @param string        $sValue2                  The second value to compare
     *
     * @return mixed
     */
    public function ifeq($sValue1, $sValue2)
    {
        $this->sCondition = '(' . Parameter::make($sValue1) . '==' . Parameter::make($sValue2) . ')';
        return $this;
    }

    /**
     * Check if a value is not equal to another before sending the request
     *
     * @param string        $sValue1                  The first value to compare
     * @param string        $sValue2                  The second value to compare
     *
     * @return mixed
     */
    public function ifne($sValue1, $sValue2)
    {
        $this->sCondition = '(' . Parameter::make($sValue1) . '!=' . Parameter::make($sValue2) . ')';
        return $this;
    }

    /**
     * Check if a value is greater than another before sending the request
     *
     * @param string        $sValue1                  The first value to compare
     * @param string        $sValue2                  The second value to compare
     *
     * @return mixed
     */
    public function ifgt($sValue1, $sValue2)
    {
        $this->sCondition = '(' . Parameter::make($sValue1) . '>' . Parameter::make($sValue2) . ')';
        return $this;
    }

    /**
     * Check if a value is greater or equal to another before sending the request
     *
     * @param string        $sValue1                  The first value to compare
     * @param string        $sValue2                  The second value to compare
     *
     * @return mixed
     */
    public function ifge($sValue1, $sValue2)
    {
        $this->sCondition = '(' . Parameter::make($sValue1) . '>=' . Parameter::make($sValue2) . ')';
        return $this;
    }

    /**
     * Check if a value is lower than another before sending the request
     *
     * @param string        $sValue1                  The first value to compare
     * @param string        $sValue2                  The second value to compare
     *
     * @return mixed
     */
    public function iflt($sValue1, $sValue2)
    {
        $this->sCondition = '(' . Parameter::make($sValue1) . '<' . Parameter::make($sValue2) . ')';
        return $this;
    }

    /**
     * Check if a value is lower or equal to another before sending the request
     *
     * @param string        $sValue1                  The first value to compare
     * @param string        $sValue2                  The second value to compare
     *
     * @return mixed
     */
    public function ifle($sValue1, $sValue2)
    {
        $this->sCondition = '(' . Parameter::make($sValue1) . '<=' . Parameter::make($sValue2) . ')';
        return $this;
    }
}
