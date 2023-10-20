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
use Jaxon\Exception\SetupException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PsrConfigMiddleware implements MiddlewareInterface
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * @var string
     */
    protected $sConfigFile;

    /**
     * The constructor
     *
     * @param Container $di
     * @param string $sConfigFile
     */
    public function __construct(Container $di, string $sConfigFile)
    {
        $this->di = $di;
        $this->sConfigFile = $sConfigFile;
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Load the config
        $this->di->getApp()->setup($this->sConfigFile);
        // Next
        return $handler->handle($request);
    }
}
