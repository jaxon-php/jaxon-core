<?php

/**
 * Call.php - The Jaxon Call
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

namespace Jaxon\Request\Call;

use Jaxon\Response\Plugin\JQuery\DomSelector;
use Jaxon\Ui\Dialogs\DialogFacade;
use Jaxon\Ui\Pagination\Paginator;
use Jaxon\Ui\View\Store;

use function array_map;
use function array_shift;
use function count;
use function func_get_args;
use function implode;

class Call extends JsCall
{
    /**
     * @var DialogFacade
     */
    protected $xDialogFacade;

    /**
     * @var Paginator
     */
    protected $xPaginator;

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
     * @param string $sName    The javascript function or method name
     * @param DialogFacade $xDialogFacade
     * @param Paginator $xPaginator
     */
    public function __construct(string $sName, DialogFacade $xDialogFacade, Paginator $xPaginator)
    {
        parent::__construct($sName);
        $this->xDialogFacade = $xDialogFacade;
        $this->xPaginator = $xPaginator;
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
        $this->sCondition = '(' . Parameter::make($xValue1) . '==' . Parameter::make($xValue2) . ')';
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
        $this->sCondition = '(' . Parameter::make($xValue1) . '!=' . Parameter::make($xValue2) . ')';
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
        $this->sCondition = '(' . Parameter::make($xValue1) . '>' . Parameter::make($xValue2) . ')';
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
        $this->sCondition = '(' . Parameter::make($xValue1) . '>=' . Parameter::make($xValue2) . ')';
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
        $this->sCondition = '(' . Parameter::make($xValue1) . '<' . Parameter::make($xValue2) . ')';
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
        $this->sCondition = '(' . Parameter::make($xValue1) . '<=' . Parameter::make($xValue2) . ')';
        return $this;
    }

    /**
     * Create parameters for message arguments
     *
     * @param array $aArgs    The arguments
     *
     * @return void
     */
    private function setMessageArgs(array $aArgs)
    {
        $this->aMessageArgs = array_map(function($xParameter) {
            return Parameter::make($xParameter);
        }, $aArgs);
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
        $this->setMessageArgs(func_get_args());
        return $this;
    }

    /**
     * Set the message to show if the condition to send the request is not met
     *
     * The first parameter is the message to show. The followings allow inserting data from
     * the webpage in the message using positional placeholders.
     *
     * @param string $sMessage    The message to show if the request is not sent
     *
     * @return Call
     */
    public function elseShow(string $sMessage): Call
    {
        $this->setMessageArgs(func_get_args());
        return $this;
    }

    /**
     * Make unique js vars for parameters of type DomSelector
     *
     * @param ParameterInterface $xParameter
     * @param array $aVariables
     * @param string $sVars
     * @param int $nVarId
     *
     * @return ParameterInterface
     */
    private function _makeUniqueJsVar(ParameterInterface $xParameter, array &$aVariables, string &$sVars, int &$nVarId): ParameterInterface
    {
        if($xParameter instanceof DomSelector)
        {
            $sParameterStr = $xParameter->getScript();
            if(!isset($aVariables[$sParameterStr]))
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
    public function getScript(): string
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
        if($this->sCondition === '__confirm__')
        {
            $sScript = $this->xDialogFacade->confirm($sPhrase, $sScript, '');
        }
        elseif($this->sCondition !== null)
        {
            $sScript = 'if(' . $this->sCondition . '){' . $sScript . ';}';
            if(($sPhrase))
            {
                $this->xDialogFacade->getMessage()->setReturn(true);
                $sScript .= 'else{' . $this->xDialogFacade->warning($sPhrase) . ';}';
                $this->xDialogFacade->getMessage()->setReturn(false);
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
     * @return bool
     */
    public function hasPageNumber(): bool
    {
        foreach($this->aParameters as $xParameter)
        {
            if($xParameter->getType() === Parameter::PAGE_NUMBER)
            {
                return true;
            }
        }
        return false;
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
        // Set the value of the Parameter::PAGE_NUMBER parameter
        foreach($this->aParameters as $xParameter)
        {
            if($xParameter->getType() === Parameter::PAGE_NUMBER)
            {
                $xParameter->setValue($nPageNumber);
                break;
            }
        }
        return $this;
    }

    /**
     * Make the pagination links for this request
     *
     * @param integer $nCurrentPage    The current page
     * @param integer $nItemsPerPage    The number of items per page
     * @param integer $nItemsTotal    The total number of items
     *
     * @return Store|null
     */
    public function paginate(int $nCurrentPage, int $nItemsPerPage, int $nItemsTotal): ?Store
    {
        return $this->xPaginator->setup($this, $nCurrentPage, $nItemsPerPage, $nItemsTotal)->render();
    }

    /**
     * Make the pagination links for this request
     *
     * @param integer $nCurrentPage    The current page
     * @param integer $nItemsPerPage    The number of items per page
     * @param integer $nItemsTotal    The total number of items
     *
     * @return Store|null
     */
    public function pg(int $nCurrentPage, int $nItemsPerPage, int $nItemsTotal): ?Store
    {
        return $this->paginate($nCurrentPage, $nItemsPerPage, $nItemsTotal);
    }

    /**
     * Make the pagination links for this request
     *
     * @param integer $nCurrentPage    The current page
     * @param integer $nItemsPerPage    The number of items per page
     * @param integer $nItemsTotal    The total number of items
     *
     * @return array
     */
    public function pages(int $nCurrentPage, int $nItemsPerPage, int $nItemsTotal): array
    {
        return $this->xPaginator->setup($this, $nCurrentPage, $nItemsPerPage, $nItemsTotal)->getPages();
    }
}
