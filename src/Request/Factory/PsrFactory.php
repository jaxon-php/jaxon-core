<?php

namespace Jaxon\Request\Factory;

/**
 * PsrFactory.php
 *
 * A factory for PSR.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\Di\Container;
use Jaxon\Request\Handler\PsrAjaxMiddleware;
use Jaxon\Request\Handler\PsrConfigMiddleware;
use Jaxon\Request\Handler\PsrRequestHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class PsrFactory
{
    /**
     * The container
     *
     * @var Container
     */
    protected $di;

    /**
     * The constructor
     *
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * Set the logger
     *
     * @param LoggerInterface $xLogger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $xLogger): PsrFactory
    {
        $this->di->setLogger($xLogger);
        return $this;
    }

    /**
     * Set the container
     *
     * @param ContainerInterface $xContainer
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $xContainer): PsrFactory
    {
        $this->di->setContainer($xContainer);
        return $this;
    }

    /**
     * Get the Jaxon ajax PSR request handler
     *
     * @return PsrRequestHandler
     */
    public function handler(): PsrRequestHandler
    {
        return $this->di->getPsrRequestHandler();
    }

    /**
     * Get the Jaxon config PSR middleware
     *
     * @param string $sConfigFile
     *
     * @return PsrConfigMiddleware
     */
    public function config(string $sConfigFile): PsrConfigMiddleware
    {
        return $this->di->getPsrConfigMiddleware($sConfigFile);
    }

    /**
     * Get the Jaxon ajax PSR middleware
     *
     * @return PsrAjaxMiddleware
     */
    public function ajax(): PsrAjaxMiddleware
    {
        return $this->di->getPsrAjaxMiddleware();
    }
}
