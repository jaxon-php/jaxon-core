<?php

/**
 * PsrRequestHandler.php
 *
 * A Psr7 Jaxon ajax request handler.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Handler\Psr;

use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Request\Handler\RequestHandler;
use Jaxon\Response\Manager\ResponseManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PsrRequestHandler implements RequestHandlerInterface
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
     * @var Translator
     */
    private $xTranslator;

    /**
     * The constructor
     *
     * @param Container $di
     * @param RequestHandler $xRequestHandler
     * @param ResponseManager $xResponseManager
     * @param Translator $xTranslator
     */
    public function __construct(Container $di, RequestHandler $xRequestHandler,
        ResponseManager $xResponseManager, Translator $xTranslator)
    {
        $this->di = $di;
        $this->xRequestHandler = $xRequestHandler;
        $this->xResponseManager = $xResponseManager;
        $this->xTranslator = $xTranslator;
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Save the request in the container. This will override the default request,
        // and the other classes will get this request from there.
        $this->di->val(ServerRequestInterface::class, $request);

        if(!$this->xRequestHandler->canProcessRequest())
        {
            // Unable to find a plugin to process the request
            throw new RequestException($this->xTranslator->trans('errors.request.plugin'));
        }

        // Process the request
        $this->xRequestHandler->processRequest();
        // Return a Psr7 response
        return $this->xResponseManager->getResponse()->toPsr();
    }
}
