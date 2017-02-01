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

class Request extends JsCall
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Manager;

    /**
     * The type of the request
     * 
     * Can be one of "function", "class" or "event".
     *
     * @var unknown
     */
    private $sType;

    /**
     * The arguments of the confirm() call
     *
     * @var string
     */
    protected $sConfirmArgs = null;

    /**
     * A condition to chech before sending this request
     *
     * @var string
     */
    protected $sCondition = null;

    /**
     * The constructor.
     * 
     * @param string        $sFunction            The javascript function
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
        // Save the arguments of the call.
        $this->sConfirmArgs = func_get_args();
        return $this;
    }

    /**
     * Add a condition to the request
     *
     * @param string        $sCondition               The condition to check
     *
     * The request is sent only if the condition is true.
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
        $this->sCondition = '!' . Parameter::make($sCondition)->getScript();
        return $this;
    }

    /**
     * Returns a string representation of the script output (javascript) from this request object
     *
     * @return string
     */
    public function getScript()
    {
        if(!is_array($this->sConfirmArgs) || count($this->sConfirmArgs) < 1)
        {
            $sJsCode = $this->getOption('core.prefix.' . $this->sType) . parent::getScript();
            if(($this->sCondition))
            {
                $sJsCode = 'if(' . $this->sCondition . '){' . $sJsCode . ';}';
            }
            return $sJsCode;
        }

        /*
         * JQuery variables sometimes depend on the context where they are used, eg. when they use $(this).
         * When a confirmation question is added, the Jaxon calls are maid in a different context,
         * making those variables invalid.
         * To avoid issues related to these context changes, the JQuery selectors values are first saved into
         * local variables which are then used in function calls.
         */
        $sVars = ''; // Array of local variables for JQuery selectors
        $nVarId = 1; // Position of the variables, starting to 1
        // This array will avoid declaring many variables with the same value.
        // The array key is the variable value, while the array value is the variable name.
        $aVariables = array();
        foreach($this->aParameters as &$xParameter)
        {
            if($xParameter instanceof \Jaxon\JQuery\Dom\Element)
            {
                if(!array_key_exists((string)$xParameter, $aVariables))
                {
                    // The value is not yet defined. A new variable is created.
                    $sVarName = "jxnVar$nVarId";
                    $aVariables[(string)$xParameter] = $sVarName;
                    $sVars .= "$sVarName=$xParameter;";
                    $xParameter = $sVarName;
                    $nVarId++;
                }
                else
                {
                    // The value is already defined. The corresponding variable is assigned.
                    $xParameter = $aVariables[(string)$xParameter];
                }
            }
        }
        $sScript = $this->getOption('core.prefix.' . $this->sType) . parent::getScript();

        $sConfirmQuestion = array_shift($this->sConfirmArgs); // The first array entry is the question.
        $sConfirmQuestion = "'" . addslashes($sConfirmQuestion) . "'"; // Wrap the question with single quotes
        $nParamId = 1;
        foreach($this->sConfirmArgs as &$xParameter)
        {
            if($xParameter instanceof \Jaxon\JQuery\Dom\Element)
            {
                if(!array_key_exists((string)$xParameter, $aVariables))
                {
                    // The value is not yet defined. A new variable is created.
                    $sVarName = "jxnVar$nVarId";
                    $aVariables[(string)$xParameter] = $sVarName;
                    $sVars .= "$sVarName=$xParameter;";
                    $xParameter = "'$nParamId':$sVarName";
                    $nVarId++;
                }
                else
                {
                    // The value is already defined. The corresponding variable is assigned.
                    $xParameter = "'$nParamId':" . $aVariables[(string)$xParameter];
                }
                $nParamId++;
            }
        }

        if(count($this->sConfirmArgs) > 0)
        {
            $sConfirmQuestion .= '.supplant({' . implode(',', $this->sConfirmArgs) . '})';
        }
        $sJsCode = $sVars . $this->getPluginManager()->getConfirm()->confirm($sConfirmQuestion, $sScript, '');
        if(($this->sCondition))
        {
            $sJsCode = 'if(' . $this->sCondition . '){' . $sJsCode . ';}';
        }
        return $sJsCode;
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
