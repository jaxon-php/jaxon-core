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

use JsonSerializable;
use Jaxon\Jaxon;

class Request implements JsonSerializable
{
    use \Jaxon\Utils\Traits\Container;

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
    public $sQuoteCharacter;
    
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
            $this->aParameters[$this->nPageNumberIndex]->setValue($nPageNumber);
        }
        return $this;
    }
    
    /**
     * Add a parameter value to the parameter list for this request
     *
     * @param string            $sType              The type of the value to be used
     * @param string            $sValue             The value to be used
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
     * @return void
     */
    public function addParameter($sType, $sValue)
    {
        $this->setParameter(count($this->aParameters), new Parameter($sType, $sValue));
    }
    
    /**
     * Set a specific parameter value
     *
     * @param integer           $nPosition                  The position of the parameter to set
     * @param Parameter         $xParameter                 The value to be used
     *
     * @return void
     */
    public function setParameter($nPosition, Parameter $xParameter)
    {
        $xParameter->xRequest = $this;
        if($xParameter->getType() == Jaxon::PAGE_NUMBER)
        {
            $this->nPageNumberIndex = $nPosition;
        }
        $this->aParameters[$nPosition] = $xParameter;
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
        $aArgs = func_get_args();
        $nArgs = func_num_args();

        // Use the String.supplant function to generate the final string
        $this->sConfirmQuestion = "'" . addslashes($sQuestion) . "'"; // Wrap the question with single quotes
        if($nArgs > 1)
        {
            $sSeparator = '';
            $this->sConfirmQuestion .= ".supplant({";
            for($i = 1; $i < $nArgs; $i++)
            {
                $this->sConfirmQuestion .= $sSeparator . "'" . $i . "':" . $aArgs[$i];
                $sSeparator = ',';
            }
            $this->sConfirmQuestion .= '})';
        }
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
        return $this->getPluginManager()->getConfirm()->confirm($this->sConfirmQuestion, $sScript);
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

    /**
     * Convert this request object to string, when converting the response into json.
     * 
     * This is a method of the JsonSerializable interface.
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->getScript();
    }
}
