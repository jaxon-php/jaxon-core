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

use Jaxon\App\Dialog\Library\DialogLibraryManager;
use Jaxon\Plugin\Response\JQuery\DomSelector;

use function array_shift;
use function implode;

class Call extends JsCall
{
    use Traits\CallConditionTrait;
    use Traits\CallMessageTrait;

    /**
     * @var DialogLibraryManager
     */
    protected $xDialogLibraryManager;

    /**
     * @var Paginator
     */
    protected $xPaginator;

    /**
     * @var array
     */
    private $aVariables;

    /**
     * @var string
     */
    private $sVars;

    /**
     * @var int
     */
    private $nVarId;

    /**
     * The constructor.
     *
     * @param string $sName    The javascript function or method name
     * @param DialogLibraryManager $xDialogLibraryManager
     * @param Paginator $xPaginator
     */
    public function __construct(string $sName, DialogLibraryManager $xDialogLibraryManager, Paginator $xPaginator)
    {
        parent::__construct($sName);
        $this->xDialogLibraryManager = $xDialogLibraryManager;
        $this->xPaginator = $xPaginator;
    }

    /**
     * Make unique js vars for parameters of type DomSelector
     *
     * @param ParameterInterface $xParameter
     *
     * @return ParameterInterface
     */
    private function _makeUniqueJsVar(ParameterInterface $xParameter): ParameterInterface
    {
        if($xParameter instanceof DomSelector)
        {
            $sParameterStr = $xParameter->getScript();
            if(!isset($this->aVariables[$sParameterStr]))
            {
                // The value is not yet defined. A new variable is created.
                $sVarName = 'jxnVar' . $this->nVarId;
                $this->aVariables[$sParameterStr] = $sVarName;
                $this->sVars .= "$sVarName=$xParameter;";
                $this->nVarId++;
            }
            else
            {
                // The value is already defined. The corresponding variable is assigned.
                $sVarName = $this->aVariables[$sParameterStr];
            }
            $xParameter = new Parameter(Parameter::JS_VALUE, $sVarName);
        }
        return $xParameter;
    }

    /**
     * Make a phrase to be displayed in js code
     *
     * @param array $aArgs
     *
     * @return string
     */
    private function makePhrase(array $aArgs): string
    {
        if(empty($aArgs))
        {
            return '';
        }
        // The first array entry is the message.
        $sPhrase = array_shift($aArgs);
        if(empty($aArgs))
        {
            return $sPhrase;
        }
        $nParamId = 1;
        foreach($aArgs as &$xParameter)
        {
            $xParameter = $this->_makeUniqueJsVar($xParameter);
            $xParameter = "'$nParamId':" . $xParameter->getScript();
            $nParamId++;
        }
        $sPhrase .= '.supplant({' . implode(',', $aArgs) . '})';
        return $sPhrase;
    }

    /**
     * Make a phrase to be displayed in js code
     *
     * @return string
     */
    private function makeMessage(): string
    {
        if(!($sPhrase = $this->makePhrase($this->aMessageArgs)))
        {
            return '';
        }
        $sMethod = $this->sMessageType;
        $xLibrary = $this->xDialogLibraryManager->getMessageLibrary();
        $xLibrary->setReturnCode(true);
        return $xLibrary->$sMethod($sPhrase);
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
        $this->sVars = ''; // Javascript code defining all the variables values.
        $this->nVarId = 1; // Position of the variables, starting from 1.
        // This array will avoid declaring multiple variables with the same value.
        // The array key is the variable value, while the array value is the variable name.
        $this->aVariables = []; // Array of local variables.
        foreach($this->aParameters as &$xParameter)
        {
            $xParameter = $this->_makeUniqueJsVar($xParameter);
        }

        $sMessageScript = $this->makeMessage();
        $sScript = parent::getScript();
        if($this->sCondition === '__confirm__')
        {
            $sConfirmPhrase = $this->makePhrase($this->aConfirmArgs);
            $sScript = $this->xDialogLibraryManager->getQuestionLibrary()
                ->confirm($sConfirmPhrase, $sScript, $sMessageScript);
        }
        elseif($this->sCondition !== '')
        {
            $sScript = empty($sMessageScript) ? 'if(' . $this->sCondition . '){' . $sScript . ';}' :
                'if(' . $this->sCondition . '){' . $sScript . ';}else{' . $sMessageScript . ';}';
        }
        return $this->sVars . $sScript;
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
     * @return array
     */
    public function pages(int $nCurrentPage, int $nItemsPerPage, int $nItemsTotal): array
    {
        // Append the page number to the parameter list, if not yet given.
        if(!$this->hasPageNumber())
        {
            $this->addParameter(Parameter::PAGE_NUMBER, 0);
        }
        return $this->xPaginator->setup($this, $nCurrentPage, $nItemsPerPage, $nItemsTotal)->pages();
    }

    /**
     * Make the pagination links for this request
     *
     * @param integer $nCurrentPage    The current page
     * @param integer $nItemsPerPage    The number of items per page
     * @param integer $nItemsTotal    The total number of items
     *
     * @return Paginator
     */
    public function paginate(int $nCurrentPage, int $nItemsPerPage, int $nItemsTotal): Paginator
    {
        // Append the page number to the parameter list, if not yet given.
        if(!$this->hasPageNumber())
        {
            $this->addParameter(Parameter::PAGE_NUMBER, 0);
        }
        return $this->xPaginator->setup($this, $nCurrentPage, $nItemsPerPage, $nItemsTotal);
    }

    /**
     * Make the pagination links for this request
     *
     * @param integer $nCurrentPage    The current page
     * @param integer $nItemsPerPage    The number of items per page
     * @param integer $nItemsTotal    The total number of items
     *
     * @return Paginator
     */
    public function pg(int $nCurrentPage, int $nItemsPerPage, int $nItemsTotal): Paginator
    {
        return $this->paginate($nCurrentPage, $nItemsPerPage, $nItemsTotal);
    }
}
