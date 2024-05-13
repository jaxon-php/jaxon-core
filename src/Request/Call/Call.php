<?php

/**
 * Call.php - The Jaxon Call
 *
 * This class is used to create js ajax requests to callable classes and functions.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Call;

use Jaxon\App\Dialog\DialogManager;

use function array_shift;
use function func_get_args;

class Call extends JsCall
{
    /**
     * @var DialogManager
     */
    protected $xDialogManager;

    /**
     * The arguments of the else() calls
     *
     * @var array
     */
    protected $aMessage = [];

    /**
     * A condition to check before making the call
     *
     * @var array
     */
    protected $aCondition = [];

    /**
     * The arguments of the confirm() call
     *
     * @var array
     */
    protected $aConfirm = [];

    /**
     * The constructor.
     *
     * @param string $sName    The javascript function or method name
     * @param DialogManager $xDialogManager
     */
    public function __construct(string $sName, DialogManager $xDialogManager)
    {
        parent::__construct($sName);
        $this->xDialogManager = $xDialogManager;
    }

    /**
     * @param array $aArgs
     *
     * @return array
     */
    private function getArgs(array $aArgs): array
    {
        array_shift($aArgs);
        return $aArgs;
    }

    /**
     * Show a message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseShow(string $sMessage): Call
    {
        $this->aMessage = $this->xDialogManager->warning($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show an information message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseInfo(string $sMessage): Call
    {
        $this->aMessage = $this->xDialogManager->info($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show a success message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseSuccess(string $sMessage): Call
    {
        $this->aMessage = $this->xDialogManager->success($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show a warning message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseWarning(string $sMessage): Call
    {
        $this->aMessage = $this->xDialogManager->warning($sMessage, $this->getArgs(func_get_args()));
        return $this;
    }

    /**
     * Show an error message if the condition to the call is not met
     *
     * @param string $sMessage  The message to show
     *
     * @return Call
     */
    public function elseError(string $sMessage): Call
    {
        $this->aMessage = $this->xDialogManager->error($sMessage, $this->getArgs(func_get_args()));
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
        $this->aConfirm = $this->xDialogManager->confirm($sQuestion, $this->getArgs(func_get_args()));
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
        $this->aCondition = ['eq', Parameter::make($xValue1), Parameter::make($xValue2)];
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
        $this->aCondition = ['teq', Parameter::make($xValue1), Parameter::make($xValue2)];
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
        $this->aCondition = ['ne', Parameter::make($xValue1), Parameter::make($xValue2)];
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
        $this->aCondition = ['nte', Parameter::make($xValue1), Parameter::make($xValue2)];
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
        $this->aCondition = ['gt', Parameter::make($xValue1), Parameter::make($xValue2)];
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
        $this->aCondition = ['ge', Parameter::make($xValue1), Parameter::make($xValue2)];
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
        $this->aCondition = ['lt', Parameter::make($xValue1), Parameter::make($xValue2)];
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
        $this->aCondition = ['le', Parameter::make($xValue1), Parameter::make($xValue2)];
        return $this;
    }

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
        return $this->ifeq(true, $xCondition);
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
        return $this->ifeq(false, $xCondition);
    }

    /**
     * Convert this call to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $aCall = parent::toArray();
        if(($this->aConfirm))
        {
            $aCall['question'] = $this->aConfirm;
        }
        if(($this->aCondition))
        {
            $aCall['condition'] = $this->aCondition;
        }
        if(($this->aMessage))
        {
            $aCall['message'] = $this->aMessage;
        }
        return $aCall;
    }

    /**
     * Check if the request has a parameter of type Parameter::PAGE_NUMBER
     *
     * @return ParameterInterface|null
     */
    private function findPageNumber(): ?ParameterInterface
    {
        foreach($this->aParameters as $xParameter)
        {
            if($xParameter->getType() === Parameter::PAGE_NUMBER)
            {
                return $xParameter;
            }
        }
        return null;
    }

    /**
     * Check if the request has a parameter of type Parameter::PAGE_NUMBER
     *
     * @return bool
     */
    public function hasPageNumber(): bool
    {
        return $this->findPageNumber() !== null;
    }

    /**
     * Set a value to the Parameter::PAGE_NUMBER parameter
     *
     * @param integer $nPageNumber    The current page number
     *
     * @return Call
     */
    public function setPageNumber(int $nPageNumber): Call
    {
        /** @var Parameter */
        $xParameter = $this->findPageNumber();
        if($xParameter !== null)
        {
            $xParameter->setValue($nPageNumber);
        }
        return $this;
    }
}
