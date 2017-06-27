<?php

/**
 * Response.php - Jaxon Response interface
 *
 * Interface for Jaxon Response plugins.
 *
 * A response plugin provides additional services not already provided by the
 * <Jaxon\Response\Response> class with regard to sending response commands to the client.
 * In addition, a response command may send javascript to the browser at page load
 * to aid in the processing of it's response commands.
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

namespace Jaxon\Plugin;

abstract class Response extends Plugin
{
    /**
     * The object used to build the response that will be sent to the client browser
     *
     * @var \Jaxon\Response\Response
     */
    protected $xResponse;
    
    /**
     * Set the <Jaxon\Response\Response> object
     *
     * @param \Jaxon\Response\Response         $xResponse            The response
     *
     * @return void
     */
    public function setResponse($xResponse)
    {
        $this->xResponse = $xResponse;
    }
    
    /**
     * Get the <Jaxon\Response\Response> object
     *
     * @return \Jaxon\Response\Response
     */
    public function response()
    {
        return $this->xResponse;
    }
    
    /**
     * Add a client side plugin command to the response object
     *
     * Used internally to add a command to the response command list.
     * This will call <Jaxon\Response\Response->addPluginCommand> using the
     * reference provided in <Jaxon\Response\Response->setResponse>.
     *
     * @param array         $aAttributes        The attributes of the command
     * @param string        $sData                The data to be added to the command
     *
     * @return void
     */
    public function addCommand($aAttributes, $sData)
    {
        $this->xResponse->addPluginCommand($this, $aAttributes, $sData);
    }

    /**
     * Return true if the object is a response plugin. Always return true here.
     *
     * @return boolean
     */
    public function isResponse()
    {
        return true;
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page
     *
     * The code must be enclosed in the appropriate HTML tags.
     *
     * @return string
     */
    public function getJs()
    {
        return '';
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page
     *
     * The code must be enclosed in the appropriate HTML tags.
     *
     * @return string
     */
    public function getCss()
    {
        return '';
    }
}
