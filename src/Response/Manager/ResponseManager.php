<?php

/**
 * ResponseManager.php - Jaxon ResponsePlugin PluginManager
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

use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Response\ResponseInterface;
use Jaxon\Utils\Translation\Translator;

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
    private $xResponse;

    /**
     * The debug messages
     *
     * @var array
     */
    private $aDebugMessages = [];

    /**
     * The class constructor
     *
     * @param string $sCharacterEncoding
     * @param Container $di
     * @param Translator $xTranslator
     */
    public function __construct(string $sCharacterEncoding, Container $di, Translator $xTranslator)
    {
        $this->di = $di;
        $this->sCharacterEncoding = $sCharacterEncoding;
        $this->xTranslator = $xTranslator;
        // By default, use the global response;
        $this->xResponse = $di->getResponse();
    }

    /**
     * Clear the current response
     *
     * @return void
     */
    public function clear()
    {
        $this->xResponse->clearCommands();
    }

    /**
     * Get the response to the Jaxon request
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
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
     * @param ResponseInterface $xResponse The response object to be appended
     *
     * @return void
     */
    public function append(ResponseInterface $xResponse)
    {
        if($this->xResponse->getCommandCount() === 0)
        {
            $this->xResponse = $xResponse;
            return;
        }
        if(get_class($this->xResponse) !== get_class($xResponse))
        {
            $this->debug($this->xTranslator->trans('errors.mismatch.types', ['class' => get_class($xResponse)]));
            return;
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
            $this->xResponse->debug($sMessage);
        }
        $this->aDebugMessages = [];
    }

    /**
     * Get the type and content of the HTTP response
     *
     * @return array
     */
    public function getResponseContent(): array
    {
        if($this->xResponse->getCommandCount() === 0)
        {
            return [];
        }
        $sType = $this->xResponse->getContentType();
        if(!empty($this->sCharacterEncoding))
        {
            $sType .= '; charset="' . $this->sCharacterEncoding . '"';
        }
        return ['type' => $sType, 'content' => $this->xResponse->getOutput()];
    }
}
