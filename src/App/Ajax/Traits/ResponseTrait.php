<?php

/**
 * ResponseTrait.php
 *
 * Jaxon ajax response content.
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

use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\Response;

trait ResponseTrait
{
    /**
     * @return ResponseManager
     */
    abstract public function getResponseManager(): ResponseManager;

    /**
     * Get the global Response object
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->getResponseManager()->getResponse();
    }

    /**
     * Create a new Jaxon response object
     *
     * @return Response
     */
    public function newResponse(): Response
    {
        return $this->getResponseManager()->newResponse();
    }

    /**
     * Get the Jaxon ajax response
     *
     * @return AjaxResponse
     */
    public function ajaxResponse(): AjaxResponse
    {
        return $this->getResponseManager()->ajaxResponse();
    }

    /**
     * Get the configured character encoding
     *
     * @return string
     */
    public function getCharacterEncoding(): string
    {
        return $this->getResponseManager()->getCharacterEncoding();
    }

    /**
     * Get the content type of the HTTP response
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->getResponseManager()->getContentType();
    }
}
