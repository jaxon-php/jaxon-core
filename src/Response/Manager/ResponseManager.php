<?php

/**
 * ResponseManager.php - Jaxon Response Manager
 *
 * This class stores and tracks the response that will be returned after processing a request.
 * The Response Manager represents a single point of contact for working with <ResponsePlugin> objects.
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

namespace Jaxon\Response\Manager;

use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Response\ResponseInterface;

use function get_class;

class ResponseManager
{
    /**
     * @var Container
     */
    private $di;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * @var string
     */
    private $sCharacterEncoding;

    /**
     * The current response object that will be sent back to the browser
     * once the request processing phase is complete
     *
     * @var ResponseInterface
     */
    private $xResponse = null;

    /**
     * The debug messages
     *
     * @var array
     */
    private $aDebugMessages = [];

    /**
     * @param string $sCharacterEncoding
     * @param Container $di
     * @param Translator $xTranslator
     */
    public function __construct(string $sCharacterEncoding, Container $di, Translator $xTranslator)
    {
        $this->di = $di;
        $this->sCharacterEncoding = $sCharacterEncoding;
        $this->xTranslator = $xTranslator;
    }

    /**
     * Clear the current response
     *
     * @return void
     */
    public function clear()
    {
        if($this->xResponse !== null)
        {
            $this->xResponse->clearCommands();
        }
        $this->di->getResponse()->clearCommands();
    }

    /**
     * Get the response to the Jaxon request
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        if(!$this->xResponse)
        {
            $this->xResponse = $this->di->getResponse();
        }
        return $this->xResponse;
    }

    /**
     * Append one response object onto the end of another
     *
     * You cannot append a given response onto the end of a response of different type.
     * If no prior response has been appended, this response becomes the main response
     * object to which other response objects will be appended.
     *
     * @param ResponseInterface $xResponse The response object to be appended
     *
     * @return void
     * @throws RequestException
     */
    public function append(ResponseInterface $xResponse)
    {
        if(!$this->xResponse)
        {
            $this->xResponse = $this->di->getResponse();
        }
        if($this->xResponse->getCommandCount() === 0)
        {
            $this->xResponse = $xResponse;
            return;
        }
        if(get_class($this->xResponse) !== get_class($xResponse))
        {
            throw new RequestException($this->xTranslator->trans('errors.mismatch.types',
                ['class' => get_class($xResponse)]));
        }
        if($this->xResponse !== $xResponse)
        {
            $this->xResponse->appendResponse($xResponse);
        }
    }

    /**
     * Appends a debug message on the end of the debug message queue
     *
     * Debug messages will be sent to the client with the normal response
     * (if the response object supports the sending of debug messages, see: <ResponsePlugin>)
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
     * @param string $sMessage The debug message
     *
     * @return void
     */
    public function error(string $sMessage)
    {
        $this->clear();
        $this->debug($sMessage);
    }

    /**
     * Prints the debug messages into the current response object
     *
     * @return void
     */
    public function printDebug()
    {
        foreach($this->aDebugMessages as $sMessage)
        {
            $this->getResponse()->debug($sMessage);
        }
        $this->aDebugMessages = [];
    }

    /**
     * Get the content type of the HTTP response
     *
     * @return string
     */
    public function getContentType(): string
    {
        return empty($this->sCharacterEncoding) ? $this->getResponse()->getContentType() :
            $this->getResponse()->getContentType() . '; charset="' . $this->sCharacterEncoding . '"';
    }

    /**
     * Get the JSON output of the response
     *
     * @return string
     */
    public function getOutput(): string
    {
        return $this->getResponse()->getOutput();
    }
}
