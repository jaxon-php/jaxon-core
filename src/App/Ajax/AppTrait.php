<?php

/**
 * AppTrait.php
 *
 * Jaxon application
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax;

use Jaxon\App\Ajax\Bootstrap;
use Jaxon\App\Ajax\Lib;
use Jaxon\Exception\RequestException;
use Jaxon\Response\AjaxResponse;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Closure;

trait AppTrait
{
    use LibTrait;
    use AppConfigTrait;

    /**
     * @return void
     */
    private function initApp()
    {
        $this->xContainer = Lib::getInstance()->di();
        $this->xComponentContainer = Lib::getInstance()->cdi();
    }

    /**
     * Get the Jaxon application bootstrapper.
     *
     * @return Bootstrap
     */
    protected function bootstrap(): Bootstrap
    {
        return $this->di()->getBootstrap();
    }

    /**
     * Set the ajax endpoint URI
     *
     * @param string $sUri    The ajax endpoint URI
     *
     * @return void
     */
    public function uri(string $sUri)
    {
        $this->setOption('core.request.uri', $sUri);
    }

    /**
     * Set the javascript asset
     *
     * @param bool $bExport    Whether to export the js code in a file
     * @param bool $bMinify    Whether to minify the exported js file
     * @param string $sUri    The URI to access the js file
     * @param string $sDir    The directory where to create the js file
     *
     * @return void
     */
    public function asset(bool $bExport, bool $bMinify, string $sUri = '', string $sDir = '')
    {
        $this->bootstrap()->asset($bExport, $bMinify, $sUri, $sDir);
    }

    /**
     * Get the HTTP response
     *
     * @param string $sCode    The HTTP response code
     *
     * @return mixed
     */
    abstract public function httpResponse(string $sCode = '200');

    /**
     * Get the Jaxon ajax response
     *
     * @return AjaxResponse
     */
    public function ajaxResponse(): AjaxResponse
    {
        return $this->getResponseManager()->getResponse();
    }

    /**
     * Process an incoming Jaxon request, and return the response.
     *
     * @return void
     * @throws RequestException
     */
    public function processRequest()
    {
        $this->di()->getRequestHandler()->processRequest();
    }

    /**
     * Set the container provided by the integrated framework
     *
     * @param ContainerInterface $xContainer    The container implementation
     *
     * @return void
     */
    public function setContainer(ContainerInterface $xContainer)
    {
        $this->di()->setContainer($xContainer);
    }

    /**
     * Add a view renderer with an id
     *
     * @param string $sRenderer    The renderer name
     * @param string $sExtension    The extension to append to template names
     * @param Closure $xClosure    A closure to create the view instance
     *
     * @return void
     */
    public function addViewRenderer(string $sRenderer, string $sExtension, Closure $xClosure)
    {
        $this->di()->getViewRenderer()->setDefaultRenderer($sRenderer, $sExtension, $xClosure);
    }

    /**
     * Set the logger.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->di()->setLogger($logger);
    }

    /**
     * Set the session manager
     *
     * @param Closure $xClosure    A closure to create the session manager instance
     *
     * @return void
     */
    public function setSessionManager(Closure $xClosure)
    {
        $this->di()->setSessionManager($xClosure);
    }

    /**
     * @inheritDoc
     */
    public function setup(string $sConfigFile = '')
    {}
}
