<?php

/**
 * BrowserEvent.php - The Jaxon browser event plugin
 *
 * This class stores a reference to a user defined event which can be triggered from client side
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

namespace Jaxon\Request\Support;

use Jaxon\Request\Request;
use Jaxon\Request\Manager as RequestManager;
use Jaxon\Response\Manager as ResponseManager;

class BrowserEvent
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Template;

    /**
     * The name of the event
     *
     * @var string
     */
    private $sName;
    
    /**
     * Configuration / call options to be used when initiating a jaxon request to trigger this event
     *
     * @var array
     */
    private $aConfiguration;
    
    /**
     * A list of <\Jaxon\Request\Support\UserFunction> objects associated with this registered event
     *
     * Each of these functions will be called when the event is triggered.
     *
     * @var array
     */
    private $aHandlers;
    
    public function __construct($sName)
    {
        $this->sName = $sName;
        $this->aConfiguration = array();
        $this->aHandlers = array();
    }
    
    /**
     * Return the name of the event
     *
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }
    
    /**
     * Sets/stores configuration options that will be used when generating
     * the client script that is sent to the browser
     *
     * @param string        $sName                The name of the configuration option
     * @param string        $sValue               The value of the configuration option
     *
     * @return void
     */
    public function configure($sName, $sValue)
    {
        $this->aConfiguration[$sName] = $sValue;
    }
    
    /**
     * Add a <\Jaxon\Request\Support\UserFunction> object to the list of handlers
     * that will be fired when the event is triggered
     *
     * @param \Jaxon\Request\Support\UserFunction        $xUserFunction        The user function
     *
     * @return void
     */
    public function addHandler($xUserFunction)
    {
        $this->aHandlers[] = $xUserFunction;
    }
    
    /**
     * Generate a <\Jaxon\Request\Request> object that corresponds to the event
     * so that the client script can easily invoke this event
     *
     * @return \Jaxon\Request\Request
     */
    public function generateRequest()
    {
        $sEvent = $this->sName;
        return new Request($sEvent, 'event');
    }

    /**
     * Generate the javascript code that declares a stub function that can be used
     * to easily trigger the event from the browser
     *
     * @return string
     */
    public function getScript()
    {
        $sEventPrefix = $this->getOption('core.prefix.event');
        $sMode = '';
        $sMethod = '';
        if(isset($this->aConfiguration['mode']))
        {
            $sMode = $this->aConfiguration['mode'];
        }
        if(isset($this->aConfiguration['method']))
        {
            $sMethod = $this->aConfiguration['method'];
        }

        return $this->render('jaxon::support/event.js', array(
            'sPrefix' => $sEventPrefix,
            'sEvent' => $this->sName,
            'sMode' => $sMode,
            'sMethod' => $sMethod,
        ));
    }
    
    /**
     * Called by the <\Jaxon\Request\Plugin\BrowserEvent> plugin when the event has been triggered
     *
     * @param array         $aArgs                The arguments for the handlers
     *
     * @return void
     */
    public function fire($aArgs)
    {
        foreach($this->aHandlers as $xHandler)
        {
            $xHandler->call($aArgs);
        }
    }
}
