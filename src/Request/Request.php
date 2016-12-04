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
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request;

use Jaxon\Jaxon;

class Request
{
    use \Jaxon\Utils\ContainerTrait;

    /**
     * The name of an Jaxon function or a method of a callable object
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
    protected $sQuoteCharacter;
    
    /**
     * An array of parameters that will be used to populate the argument list for this function
     * when the javascript is output in <Request->getScript>
     *
     * @var array
     */
    protected $aParameters;
    
    /**
     * The position of the Jaxon::PAGE_NUMBER parameter in the parameters array
     *
     * @var integer
     */
    protected $nPageNumberIndex;
    
    /**
     * A confirmation question which is asked to the user before sending this request
     *
     * @var string
     */
    protected $sConfirmQuestion = null;
    
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
     * Instruct the request to use single quotes when generating the javascript
     *
     * @return void
     */
    public function useSingleQuotes()
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
     * Instruct the request to use double quotes while generating the javascript
     *
     * @return void
     */
    public function useDoubleQuotes()
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
     * Check if the request has a parameter of type Jaxon::PAGE_NUMBER
     *
     * @return boolean
     */
    public function hasPageNumber()
    {
        return ($this->nPageNumberIndex >= 0);
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
     * Types should be one of the following <Jaxon::FORM_VALUES>, <Jaxon::QUOTED_VALUE>, <Jaxon::NUMERIC_VALUE>,
     * <Jaxon::JS_VALUE>, <Jaxon::INPUT_VALUE>, <Jaxon::CHECKED_VALUE>, <Jaxon::PAGE_NUMBER>.
     * The value should be as follows:
     * - <Jaxon::FORM_VALUES> - Use the ID of the form you want to process.
     * - <Jaxon::QUOTED_VALUE> - The string data to be passed.
     * - <Jaxon::JS_VALUE> - A string containing valid javascript
     *   (either a javascript variable name that will be in scope at the time of the call or
     *   a javascript function call whose return value will become the parameter).
     *
     * @param integer         $nParameter            The index of the parameter to set
     * @param string        $sType                The type of the value to be used
     * @param string        $sValue                The value to be used
     *
     * @return void
     */
    public function setParameter($nParameter, $sType, $xValue)
    {
        switch($sType)
        {
        case Jaxon::FORM_VALUES:
            $sFormID = (string)$xValue;
            $this->aParameters[$nParameter] = "jaxon.getFormValues(" . $this->sQuoteCharacter 
                . $sFormID . $this->sQuoteCharacter . ")";
            break;
        case Jaxon::INPUT_VALUE:
            $sInputID = (string)$xValue;
            $this->aParameters[$nParameter] =  "jaxon.$("  . $this->sQuoteCharacter 
                . $sInputID . $this->sQuoteCharacter  . ").value";
            break;
        case Jaxon::CHECKED_VALUE:
            $sCheckedID = (string)$xValue;
            $this->aParameters[$nParameter] =  "jaxon.$("  . $this->sQuoteCharacter 
                . $sCheckedID  . $this->sQuoteCharacter . ").checked";
            break;
        case Jaxon::ELEMENT_INNERHTML:
            $sElementID = (string)$xValue;
            $this->aParameters[$nParameter] = "jaxon.$(" . $this->sQuoteCharacter 
                . $sElementID . $this->sQuoteCharacter . ").innerHTML";
            break;
        case Jaxon::QUOTED_VALUE:
            $this->aParameters[$nParameter] = $this->sQuoteCharacter . addslashes($xValue) . $this->sQuoteCharacter;
            break;
        case Jaxon::BOOL_VALUE:
            $this->aParameters[$nParameter] = ($xValue) ? 'true' : 'false';
            break;
        case Jaxon::PAGE_NUMBER:
            $this->nPageNumberIndex = $nParameter;
            $this->aParameters[$nParameter] = (string)$xValue;
            break;
        case Jaxon::NUMERIC_VALUE:
            $this->aParameters[$nParameter] = (string)$xValue;
            break;
        case Jaxon::JS_VALUE:
            if(is_array($xValue) || is_object($xValue))
            {
                // Unable to use double quotes here because they cannot be handled on client side.
                // So we are using simple quotes even if the Json standard recommends double quotes.
                $this->aParameters[$nParameter] = str_replace(['"'], ["'"], json_encode($xValue, JSON_HEX_APOS | JSON_HEX_QUOT));
            }
            else
            {
                $this->aParameters[$nParameter] = (string)$xValue;
            }
            break;
        }
    }

    /**
     * Add a confirmation question to the request
     *
     * @param string        $sQuestion                The question to ask before calling this function
     *
     * @return Request
     */
    public function confirm($sQuestion)
    {
        $this->sConfirmQuestion = $sQuestion;
        return $this;
    }

    /**
     * Returns a string representation of the script output (javascript) from this request object
     *
     * @return string
     */
    public function getScript()
    {
        $sJaxonPrefix = $this->getOption('core.prefix.' . $this->sType);
        $sScript = $sJaxonPrefix . $this->sName . '(' . implode(', ', $this->aParameters) . ')';
        if(!$this->sConfirmQuestion)
        {
            return $sScript;
        }
        return $this->getPluginManager()->getConfirm()->getScriptWithQuestion($this->sConfirmQuestion, $sScript);
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
