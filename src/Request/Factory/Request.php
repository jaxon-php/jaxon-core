<?php

/**
 * Request.php - The Jaxon Request
 *
 * This class is used to create client side requests to callable classes and functions.
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

namespace Jaxon\Request\Factory;

use Jaxon\Request\Factory\Parameter;
use Jaxon\Request\Factory\Contracts\Parameter as ParameterContract;
use Jaxon\Response\Plugin\JQuery\Dom\Element as DomElement;

class Request extends JsCall
{
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
     */
    public function __construct($sName)
    {
        parent::__construct($sName);
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
     * Create parameters for message arguments
     *
     * @param array     $aArgs          The arguments
     *
     * @return void
     */
    private function setMessageArgs(array $aArgs)
    {
        array_walk($aArgs, function(&$xParameter) {
            $xParameter = Parameter::make($xParameter);
        });
        $this->aMessageArgs = $aArgs;
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
        $this->setMessageArgs(func_get_args());
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
        $this->setMessageArgs(func_get_args());
        return $this;
    }

    /**
     * Make unique js vars for parameters of type DomElement
     *
     * @var ParameterContract   $xParameter
     * @var array               &$aVariables
     * @var string              &$sVars
     * @var integer             &$nVarId
     *
     * @return ParameterContract
     */
    private function _makeUniqueJsVar(ParameterContract $xParameter, array &$aVariables, &$sVars, &$nVarId)
    {
        if($xParameter instanceof DomElement)
        {
            $sParameterStr = $xParameter->getScript();
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
            $xParameter = new Parameter(Parameter::JS_VALUE, $sVarName);
        }
        return $xParameter;
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
        $aVariables = []; // Array of local variables.
        foreach($this->aParameters as &$xParameter)
        {
            $xParameter = $this->_makeUniqueJsVar($xParameter, $aVariables, $sVars, $nVarId);
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
                    $xParameter = $this->_makeUniqueJsVar($xParameter, $aVariables, $sVars, $nVarId);
                    $xParameter = "'$nParamId':" . $xParameter->getScript();
                    $nParamId++;
                }
                $sPhrase .= '.supplant({' . implode(',', $this->aMessageArgs) . '})';
            }
        }

        $sScript = parent::getScript();
        $xDialog = jaxon()->dialog();
        if($this->sCondition == '__confirm__')
        {
            $sScript = $xDialog->confirm($sPhrase, $sScript, '');
        }
        elseif($this->sCondition !== null)
        {
            $sScript = 'if(' . $this->sCondition . '){' . $sScript . ';}';
            if(($sPhrase))
            {
                $xDialog->getMessage()->setReturn(true);
                $sScript .= 'else{' . $xDialog->warning($sPhrase) . ';}';
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

    /**
     * Check if the request has a parameter of type Parameter::PAGE_NUMBER
     *
     * @return boolean
     */
    public function hasPageNumber()
    {
        foreach($this->aParameters as $xParameter)
        {
            if($xParameter->getType() == Parameter::PAGE_NUMBER)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Set a value to the Parameter::PAGE_NUMBER parameter
     *
     * @param integer        $nPageNumber        The current page number
     *
     * @return Request
     */
    public function setPageNumber($nPageNumber)
    {
        // Set the value of the Parameter::PAGE_NUMBER parameter
        foreach($this->aParameters as $xParameter)
        {
            if($xParameter->getType() == Parameter::PAGE_NUMBER)
            {
                $xParameter->setValue(intval($nPageNumber));
                break;
            }
        }
        return $this;
    }

    /**
     * Make the pagination links for this request
     *
     * @param integer       $nCurrentPage           The current page
     * @param integer       $nItemsPerPage          The number of items per page page
     * @param integer       $nItemsTotal            The total number of items
     *
     * @return \Jaxon\Utils\Pagination\Paginator
     */
    public function pg($nCurrentPage, $nItemsPerPage, $nItemsTotal)
    {
        return jaxon()->di()->getPaginator()
            ->setup($nItemsTotal, $nItemsPerPage, $nCurrentPage, $this);
    }

    /**
     * Make the pagination links for this request
     *
     * @param integer       $nCurrentPage           The current page
     * @param integer       $nItemsPerPage          The number of items per page page
     * @param integer       $nItemsTotal            The total number of items
     *
     * @return \Jaxon\Utils\Pagination\Paginator
     */
    public function paginate($nCurrentPage, $nItemsPerPage, $nItemsTotal)
    {
        return $this->pg($nCurrentPage, $nItemsPerPage, $nItemsTotal);
    }
}
