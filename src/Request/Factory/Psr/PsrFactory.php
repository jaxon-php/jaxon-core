<?php

namespace Jaxon\Request\Factory\Psr;

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
use Jaxon\Request\Handler\Psr\PsrAjaxMiddleware;
use Jaxon\Request\Handler\Psr\PsrConfigMiddleware;
use Jaxon\Request\Handler\Psr\PsrRequestHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Closure;

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
    public function logger(LoggerInterface $xLogger): PsrFactory
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
    public function container(ContainerInterface $xContainer): PsrFactory
    {
        $this->di->setContainer($xContainer);
        return $this;
    }

    /**
     * Add a view renderer with an id
     *
     * @param string $sRenderer    The renderer name
     * @param string $sExtension    The extension to append to template names
     * @param Closure $xClosure    A closure to create the view instance
     *
     * @return $this
     */
    public function view(string $sRenderer, string $sExtension, Closure $xClosure): PsrFactory
    {
        $this->di->getViewRenderer()->setDefaultRenderer($sRenderer, $sExtension, $xClosure);
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
