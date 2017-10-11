<?php

/**
 * BrowserEvent.php - Jaxon browser event
 *
 * This class adds server side event handling capabilities to Jaxon
 *
 * Events can be registered, then event handlers attached.
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

namespace Jaxon\Request\Plugin;

use Jaxon\Jaxon;
use Jaxon\Plugin\Request as RequestPlugin;

class BrowserEvent extends RequestPlugin
{
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Validator;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * The registered browser events
     *
     * @var array
     */
    protected $aEvents;

    /**
     * The name of the event that is being requested (during the request processing phase)
     *
     * @var string
     */
    protected $sRequestedEvent;

    public function __construct()
    {
        $this->aEvents = array();

        $this->sRequestedEvent = null;

        if(isset($_GET['jxnevt']))
        {
            $this->sRequestedEvent = $_GET['jxnevt'];
        }
        if(isset($_POST['jxnevt']))
        {
            $this->sRequestedEvent = $_POST['jxnevt'];
        }
    }

    /**
     * Return the name of this plugin
     *
     * @return string
     */
    public function getName()
    {
        return Jaxon::BROWSER_EVENT;
    }

    /**
     * Register a browser event
     *
     * @param array         $aArgs                An array containing the event specification
     *
     * @return \Jaxon\Request\Request
     */
    public function register($aArgs)
    {
        if(count($aArgs) > 1)
        {
            $sType = $aArgs[0];

            if($sType == Jaxon::BROWSER_EVENT)
            {
                $sEvent = $aArgs[1];
                if(!isset($this->aEvents[$sEvent]))
                {
                    $xBrowserEvent = new \Jaxon\Support\BrowserEvent($sEvent);
                    if(count($aArgs) > 2 && is_array($aArgs[2]))
                    {
                        foreach($aArgs[2] as $sKey => $sValue)
                        {
                            $xBrowserEvent->configure($sKey, $sValue);
                        }
                    }
                    $this->aEvents[$sEvent] = $xBrowserEvent;
                    return $xBrowserEvent->generateRequest();
                }
            }
            elseif($sType == Jaxon::EVENT_HANDLER)
            {
                $sEvent = $aArgs[1];
                if(isset($this->aEvents[$sEvent]) && isset($aArgs[2]))
                {
                    $xUserFunction = $aArgs[2];
                    if(!($xUserFunction instanceof \Jaxon\Request\Support\UserFunction))
                    {
                        $xUserFunction = new \Jaxon\Request\Support\UserFunction($xUserFunction);
                    }
                    $objEvent = $this->aEvents[$sEvent];
                    $objEvent->addHandler($xUserFunction);
                    return true;
                }
            }
        }

        return null;
    }

    /**
     * Generate a hash for the registered browser events
     *
     * @return string
     */
    public function generateHash()
    {
        $sHash = '';
        foreach($this->aEvents as $xEvent)
        {
            $sHash .= $xEvent->getName();
        }
        return md5($sHash);
    }

    /**
     * Generate client side javascript code for the registered browser events
     *
     * @return string
     */
    public function getScript()
    {
        $sCode = '';
        foreach($this->aEvents as $xEvent)
        {
            $sCode .= $xEvent->getScript();
        }
        return $sCode;
    }

    /**
     * Check if this plugin can process the incoming Jaxon request
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        // Check the validity of the event name
        if(($this->sRequestedEvent) && !$this->validateEvent($this->sRequestedEvent))
        {
            $this->sRequestedEvent = null;
        }
        return ($this->sRequestedEvent != null);
    }

    /**
     * Process the incoming Jaxon request
     *
     * @return boolean
     */
    public function processRequest()
    {
        if(!$this->canProcessRequest())
            return false;

        $aArgs = $this->getRequestManager()->process();

        if(array_key_exists($this->sRequestedEvent, $this->aEvents))
        {
            $this->aEvents[$this->sRequestedEvent]->fire($aArgs);
            return true;
        }
        // Unable to find the requested event
        throw new \Jaxon\Exception\Error($this->trans('errors.events.invalid', array('name' => $this->sRequestedEvent)));
    }
}
