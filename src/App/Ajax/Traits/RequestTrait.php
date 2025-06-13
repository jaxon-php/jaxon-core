<?php

/**
 * ResponseTrait.php
 *
 * Send Jaxon ajax response.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Thierry Feuzeu
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax\Traits;

use Jaxon\Exception\RequestException;
use Jaxon\Request\Handler\RequestHandler;

trait RequestTrait
{
    /**
     * @return RequestHandler
     */
    abstract protected function getRequestHandler(): RequestHandler;

    /**
     * Get the HTTP response
     *
     * @param string $sCode    The HTTP response code
     *
     * @return mixed
     */
    abstract public function httpResponse(string $sCode = '200'): mixed;

    /**
     * Determine if a call is a jaxon request
     *
     * @return bool
     */
    public function canProcessRequest(): bool
    {
        return $this->getRequestHandler()->canProcessRequest();
    }

    /**
     * If this is a jaxon request, call the requested PHP function, build the response and send it back to the browser
     *
     * This is the main server side engine for Jaxon.
     * It handles all the incoming requests, including the firing of events and handling of the response.
     * If your RequestURI is the same as your web page, then this function should be called before ANY
     * headers or HTML is output from your script.
     *
     * This function may exit after the request is processed, if the 'core.process.exit' option is set to true.
     *
     * @return mixed
     *
     * @throws RequestException
     * @see <canProcessRequest>
     */
    public function processRequest(): mixed
    {
        // Process the jaxon request
        $this->getRequestHandler()->processRequest();

        return $this->httpResponse();
    }
}
