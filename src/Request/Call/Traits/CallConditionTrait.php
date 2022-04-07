<?php

namespace Jaxon\Request\Call\Traits;

use Jaxon\Request\Call\Call;
use Jaxon\Request\Call\Parameter;

use function array_map;
use function func_get_args;

trait CallConditionTrait
{
    /**
     * A condition to check before making the call
     *
     * @var string
     */
    protected $sCondition = '';

    /**
     * The arguments of the confirm() call
     *
     * @var array
     */
    protected $aConfirmArgs = [];

    /**
     * Add a condition to the request
     *
     * The request is sent only if the condition is true.
     *
     * @param mixed $xCondition    The condition to check
     *
     * @return Call
     */
    public function when($xCondition): Call
    {
        $this->sCondition = Parameter::make($xCondition)->getScript();
        return $this;
    }

    /**
     * Add a condition to the request
     *
     * The request is sent only if the condition is false.
     *
     * @param mixed $xCondition    The condition to check
     *
     * @return Call
     */
    public function unless($xCondition): Call
    {
        $this->sCondition = '!(' . Parameter::make($xCondition)->getScript() . ')';
        return $this;
    }

    /**
     * Check if a value is equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifeq($xValue1, $xValue2): Call
    {
        $this->sCondition = Parameter::make($xValue1) . '==' . Parameter::make($xValue2);
        return $this;
    }

    /**
     * Check if a value is equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifteq($xValue1, $xValue2): Call
    {
        $this->sCondition = Parameter::make($xValue1) . '===' . Parameter::make($xValue2);
        return $this;
    }

    /**
     * Check if a value is not equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifne($xValue1, $xValue2): Call
    {
        $this->sCondition = Parameter::make($xValue1) . '!=' . Parameter::make($xValue2);
        return $this;
    }

    /**
     * Check if a value is not equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifnte($xValue1, $xValue2): Call
    {
        $this->sCondition = Parameter::make($xValue1) . '!==' . Parameter::make($xValue2);
        return $this;
    }

    /**
     * Check if a value is greater than another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifgt($xValue1, $xValue2): Call
    {
        $this->sCondition = Parameter::make($xValue1) . '>' . Parameter::make($xValue2);
        return $this;
    }

    /**
     * Check if a value is greater or equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifge($xValue1, $xValue2): Call
    {
        $this->sCondition = Parameter::make($xValue1) . '>=' . Parameter::make($xValue2);
        return $this;
    }

    /**
     * Check if a value is lower than another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function iflt($xValue1, $xValue2): Call
    {
        $this->sCondition = Parameter::make($xValue1) . '<' . Parameter::make($xValue2);
        return $this;
    }

    /**
     * Check if a value is lower or equal to another before sending the request
     *
     * @param mixed $xValue1    The first value to compare
     * @param mixed $xValue2    The second value to compare
     *
     * @return Call
     */
    public function ifle($xValue1, $xValue2): Call
    {
        $this->sCondition = Parameter::make($xValue1) . '<=' . Parameter::make($xValue2);
        return $this;
    }

    /**
     * Add a confirmation question to the request
     *
     * @param string $sQuestion    The question to ask
     *
     * @return Call
     */
    public function confirm(string $sQuestion): Call
    {
        $this->sCondition = '__confirm__';
        $this->aConfirmArgs = array_map(function($xParameter) {
            return Parameter::make($xParameter);
        }, func_get_args());
        return $this;
    }
}
