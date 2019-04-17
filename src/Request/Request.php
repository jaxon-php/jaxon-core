<?php

/**
 * Request.php - The Jaxon Request
 *
 * This class is used to create client side requests to the Jaxon functions and callable objects.
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

namespace Jaxon\Request;

use JsonSerializable;
use Jaxon\Jaxon;

class Request extends JsCall
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Manager;

    /**
     * The type of the request
     *
     * Can be one of "function" or "class".
     *
     * @var string
     */
    private $sType;

    /**
     * A condition to check before sending this request
     *
     * @var string
     */
    protected $sCondition = null;

    /**
     * The arguments of the confirm() call
     *
     * @var array
     */
    protected $aMessageArgs = [];

    /**
     * The constructor.
     *
     * @param string        $sName            The javascript function or method name
     * @param string        $sType            The type (function or a method)
     */
    public function __construct($sName, $sType)
    {
        parent::__construct($sName);
        $this->sType = $sType;
    }

    /**
     * Check if the request has a parameter of type Jaxon::PAGE_NUMBER
     *
     * @return boolean
     */
    public function hasPageNumber()
    {
        foreach($this->aParameters as $xParameter)
        {
            if($xParameter->getType() == Jaxon::PAGE_NUMBER)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Set a value to the Jaxon::PAGE_NUMBER parameter
     *
     * @param integer        $nPageNumber        The current page number
     *
     * @return Request
     */
    public function setPageNumber($nPageNumber)
    {
        // Set the value of the Jaxon::PAGE_NUMBER parameter
        foreach($this->aParameters as $xParameter)
        {
            if($xParameter->getType() == Jaxon::PAGE_NUMBER)
            {
                $xParameter->setValue(intval($nPageNumber));
                break;
            }
        }
        return $this;
    }

    /**
     * Add a confirmation question to the request
     *
     * @param string        $sQuestion                The question to ask
     *
     * @return Request
     */
    public function confirm($sQuestion)
    {
        $this->sCondition = '__confirm__';
        $this->aMessageArgs = func_get_args();
        array_walk($this->aMessageArgs, function (&$xParameter) {
            $xParameter = Parameter::make($xParameter);
        });
        return $this;
    }

    /**
     * Add a condition to the request
     *
     * The request is sent only if the condition is true.
     *
     * @param string        $sCondition               The condition to check
     *
     * @return Request
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
     * @return Request
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
     * @return Request
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
     * @return Request
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
     * @return Request
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
     * @return Request
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
     * @return Request
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
     * @return Request
     */
    public function ifle($sValue1, $sValue2)
    {
        $this->sCondition = '(' . Parameter::make($sValue1) . '<=' . Parameter::make($sValue2) . ')';
        return $this;
    }

    /**
     * Set the message to show if the condition to send the request is not met
     *
     * The first parameter is the message to show. The followin allow to insert data from
     * the webpage in the message using positional placeholders.
     *
     * @param string        $sMessage                 The message to show if the request is not sent
     *
     * @return Request
     */
    public function elseShow($sMessage)
    {
        $this->aMessageArgs = func_get_args();
        array_walk($this->aMessageArgs, function (&$xParameter) {
            $xParameter = Parameter::make($xParameter);
        });
        return $this;
    }

    /**
     * Returns a string representation of the script output (javascript) from this request object
     *
     * @return string
     */
    public function getScript()
    {
        /*
         * JQuery variables sometimes depend on the context where they are used, eg. when their value depends on $(this).
         * When a confirmation question is added, the Jaxon calls are made in a different context,
         * making those variables invalid.
         * To avoid issues related to these context changes, the JQuery selectors values are first saved into
         * local variables, which are then used in Jaxon function calls.
         */
        $sVars = ''; // Javascript code defining all the variables values.
        $nVarId = 1; // Position of the variables, starting from 1.
        // This array will avoid declaring multiple variables with the same value.
        // The array key is the variable value, while the array value is the variable name.
        $aVariables = array(); // Array of local variables.
        foreach($this->aParameters as &$xParameter)
        {
            $sParameterStr = $xParameter->getScript();
            if($xParameter instanceof \Jaxon\JQuery\Dom\Element)
            {
                if(!array_key_exists($sParameterStr, $aVariables))
                {
                    // The value is not yet defined. A new variable is created.
                    $sVarName = "jxnVar$nVarId";
                    $aVariables[$sParameterStr] = $sVarName;
                    $sVars .= "$sVarName=$xParameter;";
                    $nVarId++;
                }
                else
                {
                    // The value is already defined. The corresponding variable is assigned.
                    $sVarName = $aVariables[$sParameterStr];
                }
                $xParameter = new Parameter(Jaxon::JS_VALUE, $sVarName);
            }
        }

        $sPhrase = '';
        if(count($this->aMessageArgs) > 0)
        {
            $sPhrase = array_shift($this->aMessageArgs); // The first array entry is the question.
            // $sPhrase = "'" . addslashes($sPhrase) . "'"; // Wrap the phrase with single quotes
            if(count($this->aMessageArgs) > 0)
            {
                $nParamId = 1;
                foreach($this->aMessageArgs as &$xParameter)
                {
                    $sParameterStr = $xParameter->getScript();
                    if($xParameter instanceof \Jaxon\JQuery\Dom\Element)
                    {
                        if(!array_key_exists($sParameterStr, $aVariables))
                        {
                            // The value is not yet defined. A new variable is created.
                            $sVarName = "jxnVar$nVarId";
                            $aVariables[$sParameterStr] = $sVarName;
                            $sVars .= "$sVarName=$xParameter;";
                            $nVarId++;
                        }
                        else
                        {
                            // The value is already defined. The corresponding variable is assigned.
                            $sVarName = $aVariables[$sParameterStr];
                        }
                        $xParameter = new Parameter(Jaxon::JS_VALUE, $sVarName);
                    }
                    $xParameter = "'$nParamId':" . $xParameter->getScript();
                    $nParamId++;
                }
                $sPhrase .= '.supplant({' . implode(',', $this->aMessageArgs) . '})';
            }
        }

        $sScript = $this->getOption('core.prefix.' . $this->sType) . parent::getScript();
        if($this->sCondition == '__confirm__')
        {
            $xConfirm = $this->getPluginManager()->getConfirm();
            $sScript = $xConfirm->confirm($sPhrase, $sScript, '');
        }
        elseif($this->sCondition !== null)
        {
            $sScript = 'if(' . $this->sCondition . '){' . $sScript . ';}';
            if(($sPhrase))
            {
                $xAlert = $this->getPluginManager()->getAlert();
                $xAlert->setReturn(true);
                $sScript .= 'else{' . $xAlert->warning($sPhrase) . ';}';
            }
        }
        return $sVars . $sScript;
    }

    /**
     * Prints a string representation of the script output (javascript) from this request object
     *
     * @return void
     */
    public function printScript()
    {
        print $this->getScript();
    }
}
