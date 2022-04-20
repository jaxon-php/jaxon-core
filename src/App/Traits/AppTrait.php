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

namespace Jaxon\App\Traits;

use Jaxon\App\AppInterface;
use Jaxon\App\Bootstrap;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Response\Manager\ResponseManager;
use Psr\Container\ContainerInterface;

use Closure;

trait AppTrait
{
    use AjaxTrait {
        AjaxTrait::getResponse as public ajaxResponse;
    }

    /**
     * @param Container $xContainer
     *
     * @return void
     */
    private function initApp(Container $xContainer)
    {
        $this->xContainer = $xContainer;
        // Set the attributes from the container
        $this->xConfigManager = $xContainer->g(ConfigManager::class);
        $this->xResponseManager = $xContainer->g(ResponseManager::class);
        $this->xPluginManager = $xContainer->g(PluginManager::class);
        $this->xTranslator = $xContainer->g(Translator::class);
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
     * @return AppInterface
     */
    public function asset(bool $bExport, bool $bMinify, string $sUri = '', string $sDir = ''): AppInterface
    {
        $this->bootstrap()->asset($bExport, $bMinify, $sUri, $sDir);
        return $this;
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
     * Process an incoming Jaxon request, and return the response.
     *
     * @return mixed
     * @throws RequestException
     */
    public function processRequest()
    {
        // Process the jaxon request
        $this->di()->getRequestHandler()->processRequest();

        // Return the response to the request
        return $this->httpResponse();
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
        $xViewRenderer = $this->di->getViewRenderer();
        $xViewRenderer->addNamespace('default', '', $sExtension, $sRenderer);
        $xViewRenderer->addRenderer($sRenderer, $xClosure);
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
}
