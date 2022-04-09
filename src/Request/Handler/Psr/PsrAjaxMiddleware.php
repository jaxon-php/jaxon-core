<?php

/**
 * PsrAjaxMiddleware.php
 *
 * A Psr7 middleware to process Jaxon ajax requests.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Handler\Psr;

use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Request\Handler\RequestHandler;
use Jaxon\Response\Manager\ResponseManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PsrAjaxMiddleware implements MiddlewareInterface
{
    /**
     * @var Container
     */
    private $di;

    /**
     * @var RequestHandler
     */
    private $xRequestHandler;

    /**
     * @var ResponseManager
     */
    private $xResponseManager;

    /**
     * The constructor
     *
     * @param Container $di
     * @param RequestHandler $xRequestHandler
     * @param ResponseManager $xResponseManager
     */
    public function __construct(Container $di, RequestHandler $xRequestHandler, ResponseManager $xResponseManager)
    {
        $this->di = $di;
        $this->xRequestHandler = $xRequestHandler;
        $this->xResponseManager = $xResponseManager;
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Save the request in the container. This will override the default request,
        // and the other classes will get this request from there.
        $this->di->val(ServerRequestInterface::class, $request);

        if(!$this->xRequestHandler->canProcessRequest())
        {
            // Unable to find a plugin to process the request
            return $handler->handle($request);
        }

        // Process the request
        $this->xRequestHandler->processRequest();
        // Return a Psr7 response
        return $this->xResponseManager->getResponse()->toPsr();
    }
}
