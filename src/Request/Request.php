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
    use \Jaxon\Utils\Traits\Container;

    /**
     * The type of the request
     * 
     * Can be one of "function", "class" or "event".
     *
     * @var unknown
     */
    private $sType;

    /**
     * A confirmation question which is asked to the user before sending this request
     *
     * @var string
     */
    protected $sConfirmQuestion = null;

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
        $sScript = $this->getOption('core.prefix.' . $this->sType) . parent::getScript();
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
}
