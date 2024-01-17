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

use Jaxon\App\Bootstrap;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\Request\CallableClass\CallableRegistry;
use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Response\ResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Closure;

trait AppTrait
{
    use AjaxTrait;

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
        $this->xCallableRegistry = $xContainer->g(CallableRegistry::class);
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
     * @return void
     */
    public function asset(bool $bExport, bool $bMinify, string $sUri = '', string $sDir = '')
    {
        $this->bootstrap()->asset($bExport, $bMinify, $sUri, $sDir);
    }

    /**
     * Read the options from the file, if provided, and return the config
     *
     * @param string $sConfigFile The full path to the config file
     * @param string $sConfigSection The section of the config file to be loaded
     *
     * @return ConfigManager
     */
    public function config(string $sConfigFile = '', string $sConfigSection = ''): ConfigManager
    {
        return $this->xConfigManager;
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
     * @return ResponseInterface
     */
    public function ajaxResponse(): ResponseInterface
    {
        return $this->xResponseManager->getResponse();
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
}
