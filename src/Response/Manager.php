<?php

/**
 * Manager.php - Jaxon Response Manager
 *
 * This class stores and tracks the response that will be returned after processing a request.
 * The Response Manager represents a single point of contact for working with <Response> objects.
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

namespace Jaxon\Response;

use Jaxon\Jaxon;
use Jaxon\Request\Handler\Argument;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Translation\Translator;

use function trim;
use function header;
use function strlen;
use function gmdate;
use function get_class;

class Manager
{
    /**
     * @var Jaxon
     */
    private $jaxon;

    /**
     * @var Config
     */
    private $xConfig;

    /**
     * @var Argument
     */
    private $xArgumentManager;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * The current response object that will be sent back to the browser
     * once the request processing phase is complete
     *
     * @var AbstractResponse
     */
    private $xResponse = null;

    /**
     * The debug messages
     *
     * @var array
     */
    private $aDebugMessages;

    /**
     * The class constructor
     *
     * @param Jaxon $jaxon
     * @param Config $xConfig
     * @param Argument $xArgumentManager
     * @param Translator $xTranslator
     */
    public function __construct(Jaxon $jaxon, Config $xConfig, Argument $xArgumentManager, Translator $xTranslator)
    {
        $this->jaxon = $jaxon;
        $this->xConfig = $xConfig;
        $this->xArgumentManager = $xArgumentManager;
        $this->xTranslator = $xTranslator;
        $this->aDebugMessages = [];
    }

    /**
     * Clear the current response
     *
     * A new response will need to be appended before the request processing is complete.
     *
     * @return void
     */
    public function clear()
    {
        $this->xResponse = null;
    }

    /**
     * Get the response to the Jaxon request
     *
     * @return AbstractResponse
     */
    public function getResponse(): ?AbstractResponse
    {
        return $this->xResponse;
    }

    /**
     * Append one response object onto the end of another
     *
     * You cannot append a given response onto the end of a response of different type.
     * If no prior response has been appended, this response becomes the main response
     * object to which other response objects will be appended.
     *
     * @param AbstractResponse $xResponse    The response object to be appended
     *
     * @return void
     */
    public function append(AbstractResponse $xResponse)
    {
        if(!$this->xResponse)
        {
            $this->xResponse = $xResponse;
        }
        elseif(get_class($this->xResponse) === get_class($xResponse))
        {
            if($this->xResponse !== $xResponse)
            {
                $this->xResponse->appendResponse($xResponse);
            }
        }
        else
        {
            $this->debug($this->xTranslator->trans('errors.mismatch.types', ['class' => get_class($xResponse)]));
        }
    }

    /**
     * Appends a debug message on the end of the debug message queue
     *
     * Debug messages will be sent to the client with the normal response
     * (if the response object supports the sending of debug messages, see: <Response>)
     *
     * @param string $sMessage    The debug message
     *
     * @return void
     */
    public function debug(string $sMessage)
    {
        $this->aDebugMessages[] = $sMessage;
    }

    /**
     * Clear the response and appends a debug message on the end of the debug message queue
     *
     * @param string $sMessage    The debug message
     *
     * @return void
     */
    public function error(string $sMessage)
    {
        $this->clear();
        $this->append($this->jaxon->newResponse());
        $this->debug($sMessage);
    }

    /**
     * Prints the debug messages into the current response object
     *
     * @return void
     */
    public function printDebug()
    {
        if(($this->xResponse))
        {
            foreach($this->aDebugMessages as $sMessage)
            {
                $this->xResponse->debug($sMessage);
            }
            $this->aDebugMessages = [];
        }
    }

    /**
     * Used internally to generate the response headers
     *
     * @return void
     */
    private function _sendHeaders()
    {
        if($this->xArgumentManager->requestMethodIsGet())
        {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
        }

        $sCharacterSet = '';
        $sCharacterEncoding = trim($this->xConfig->getOption('core.encoding'));
        if(strlen($sCharacterEncoding) > 0)
        {
            $sCharacterSet = '; charset="' . $sCharacterEncoding . '"';
        }

        header('content-type: ' . $this->xResponse->getContentType() . ' ' . $sCharacterSet);
    }

    /**
     * Sends the HTTP headers back to the browser
     *
     * @return void
     */
    public function sendHeaders()
    {
        if(($this->xResponse))
        {
            $this->_sendHeaders();
        }
    }

    /**
     * Get the JSON output of the response
     *
     * @return string
     */
    public function getOutput(): string
    {
        if(($this->xResponse))
        {
            return $this->xResponse->getOutput();
        }
        return '';
    }

    /**
     * Prints the response object to the output stream, thus sending the response to the browser
     *
     * @return void
     */
    public function sendOutput()
    {
        if(($this->xResponse))
        {
            $this->_sendHeaders();
            $this->xResponse->printOutput();
        }
    }
}
