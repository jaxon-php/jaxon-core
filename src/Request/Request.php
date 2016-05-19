<?php

/**
 * Request.php - The Xajax Request
 *
 * This class is used to create client side requests to the Xajax functions and callable objects.
 *
 * @package xajax-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax\Request;

use Xajax\Xajax;

class Request
{
    use \Xajax\Utils\ContainerTrait;

    /**
     * The name of an Xajax function or a method of a callable object
     *
     * @var string
     */
    private $sName;
    
    /**
     * The type of the request
     * 
     * Can be one of "function", "class" or "event".
     *
     * @var unknown
     */
    private $sType;
    
    /**
     * A string containing either a single or a double quote character that will be used
     * during the generation of the javascript for this function.
     * This can be set prior to calling <Request->getScript>
     *
     * @var string
     */
    private $sQuoteCharacter;
    
    /**
     * An array of parameters that will be used to populate the argument list for this function
     * when the javascript is output in <Request->getScript>
     *
     * @var array
     */
    private $aParameters;
    
    /**
     * The position of the Xajax::PAGE_NUMBER parameter in the parameters array
     *
     * @var integer
     */
    private $nPageNumberIndex;
    
    public function __construct($sName, $sType)
    {
        $this->aParameters = array();
        $this->nPageNumberIndex = -1;
        $this->sQuoteCharacter = '"';
        $this->sName = $sName;
        $this->sType = $sType;
    }
    
    /**
     * Instruct the request to use single quotes when generating the javascript
     *
     * @return void
     */
    public function useSingleQuote()
    {
        $this->sQuoteCharacter = "'";
    }
    
    /**
     * Instruct the request to use double quotes while generating the javascript
     *
     * @return void
     */
    public function useDoubleQuote()
    {
        $this->sQuoteCharacter = '"';
    }
    
    /**
     * Clear the parameter list associated with this request
     *
     * @return void
     */
    public function clearParameters()
    {
        $this->aParameters = array();
    }
    
    /**
     * Check if the request has a parameter of type Xajax::PAGE_NUMBER
     *
     * @return boolean
     */
    public function hasPageNumber()
    {
        return ($this->nPageNumberIndex >= 0);
    }
    
    /**
     * Set a value to the Xajax::PAGE_NUMBER parameter
     *
     * @param integer        $nPageNumber        The current page number
     *
     * @return Request
     */
    public function setPageNumber($nPageNumber)
    {
        // Set the value of the Xajax::PAGE_NUMBER parameter
        $nPageNumber = intval($nPageNumber);
        if($this->nPageNumberIndex >= 0 && $nPageNumber > 0)
        {
            $this->aParameters[$this->nPageNumberIndex] = $nPageNumber;
        }
        return $this;
    }
    
    /**
     * Add a parameter value to the parameter list for this request
     *
     * @param string        $sType                The type of the value to be used
     * @param string        $sValue                The value to be used
     *
     * @return void
     */
    public function addParameter($sType, $sValue)
    {
        $this->setParameter(count($this->aParameters), $sType, $sValue);
    }
    
    /**
     * Set a specific parameter value
     *
     * Types should be one of the following <Xajax::FORM_VALUES>, <Xajax::QUOTED_VALUE>, <Xajax::NUMERIC_VALUE>,
     * <Xajax::JS_VALUE>, <Xajax::INPUT_VALUE>, <Xajax::CHECKED_VALUE>, <Xajax::PAGE_NUMBER>.
     * The value should be as follows:
     * - <Xajax::FORM_VALUES> - Use the ID of the form you want to process.
     * - <Xajax::QUOTED_VALUE> - The string data to be passed.
     * - <Xajax::JS_VALUE> - A string containing valid javascript
     *   (either a javascript variable name that will be in scope at the time of the call or
     *   a javascript function call whose return value will become the parameter).
     *
     * @param integer         $nParameter            The index of the parameter to set
     * @param string        $sType                The type of the value to be used
     * @param string        $sValue                The value to be used
     *
     * @return void
     */
    public function setParameter($nParameter, $sType, $sValue)
    {
        switch($sType)
        {
        case Xajax::FORM_VALUES:
            $sFormID = $sValue;
            $this->aParameters[$nParameter] = "xajax.getFormValues(" . $this->sQuoteCharacter 
                . $sFormID . $this->sQuoteCharacter . ")";
            break;
        case Xajax::INPUT_VALUE:
            $sInputID = $sValue;
            $this->aParameters[$nParameter] =  "xajax.$("  . $this->sQuoteCharacter 
                . $sInputID . $this->sQuoteCharacter  . ").value";
            break;
        case Xajax::CHECKED_VALUE:
            $sCheckedID = $sValue;
            $this->aParameters[$nParameter] =  "xajax.$("  . $this->sQuoteCharacter 
                . $sCheckedID  . $this->sQuoteCharacter . ").checked";
            break;
        case Xajax::ELEMENT_INNERHTML:
            $sElementID = $sValue;
            $this->aParameters[$nParameter] = "xajax.$(" . $this->sQuoteCharacter 
                . $sElementID . $this->sQuoteCharacter . ").innerHTML";
            break;
        case Xajax::QUOTED_VALUE:
            $this->aParameters[$nParameter] = $this->sQuoteCharacter . addslashes($sValue) . $this->sQuoteCharacter;
            break;
        case Xajax::PAGE_NUMBER:
            $this->nPageNumberIndex = $nParameter;
            $this->aParameters[$nParameter] = $sValue;
            break;
        case Xajax::NUMERIC_VALUE:
        case Xajax::JS_VALUE:
            $this->aParameters[$nParameter] = $sValue;
            break;
        }
    }

    /**
     * Returns a string representation of the script output (javascript) from this request object
     *
     * @return string
     */
    public function getScript()
    {
        $sXajaxPrefix = $this->getOption('core.prefix.' . $this->sType);
        return $sXajaxPrefix . $this->sName . '(' . implode(', ', $this->aParameters) . ')';
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
     * Convert this request object to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getScript();
    }
}
