<?php

/**
 * ServicesTrait.php
 *
 * Access to the services registered in the container.
 *
 * @package jaxon-core
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax\Traits;

use Jaxon\App\Ajax\Bootstrap;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\App\Session\SessionInterface;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Plugin\Code\CodeGenerator;
use Jaxon\Plugin\Manager\PackageManager;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Request\Handler\RequestHandler;
use Jaxon\Request\Handler\Psr\PsrFactory;
use Jaxon\Request\Upload\UploadHandlerInterface;
use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Utils\Template\TemplateEngine;
use Psr\Log\LoggerInterface;
use Closure;

trait ServicesTrait
{
    use DiTrait;

    /**
     * @return ConfigManager
     */
    public function config(): ConfigManager
    {
        return $this->xContainer->config();
    }

    /**
     * @return Translator
     */
    public function translator(): Translator
    {
        return $this->di()->g(Translator::class);
    }

    /**
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->di()->getLogger();
    }

    /**
     * @return Bootstrap
     */
    protected function getBootstrap(): Bootstrap
    {
        return $this->di()->getBootstrap();
    }

    /**
     * @return PluginManager
     */
    protected function getPluginManager(): PluginManager
    {
        return $this->di()->getPluginManager();
    }

    /**
     * @return RequestHandler
     */
    protected function getRequestHandler(): RequestHandler
    {
        return $this->di()->getRequestHandler();
    }

    /**
     * @return ResponseManager
     */
    protected function getResponseManager(): ResponseManager
    {
        return $this->di()->getResponseManager();
    }

    /**
     * @return PackageManager
     */
    protected function getPackageManager(): PackageManager
    {
        return $this->di()->getPackageManager();
    }

    /**
     * @return CodeGenerator
     */
    protected function getCodeGenerator(): CodeGenerator
    {
        return $this->di()->getCodeGenerator();
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
    public function addViewRenderer(string $sRenderer, string $sExtension, Closure $xClosure): void
    {
        $this->view()->setDefaultRenderer($sRenderer, $sExtension, $xClosure);
    }

    /**
     * @param LoggerInterface|Closure $xLogger
     *
     * @return void
     */
    public function setLogger(LoggerInterface|Closure $xLogger): void
    {
        $this->di()->setLogger($xLogger);
    }

    /**
     * Set the session manager
     *
     * @param Closure $xClosure    A closure to create the session manager instance
     *
     * @return void
     */
    public function setSessionManager(Closure $xClosure): void
    {
        $this->di()->setSessionManager($xClosure);
    }

    /**
     * @return SessionInterface|null
     */
    public function session(): ?SessionInterface
    {
        return $this->di()->getSessionManager();
    }

    /**
     * @return UploadHandlerInterface|null
     */
    public function upload(): ?UploadHandlerInterface
    {
        return $this->di()->getUploadHandler();
    }

    /**
     * @return PsrFactory
     */
    public function psr(): PsrFactory
    {
        return $this->di()->getPsrFactory();
    }

    /**
     * Get the template engine
     *
     * @return TemplateEngine
     */
    public function template(): TemplateEngine
    {
        return $this->di()->getTemplateEngine();
    }

    /**
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    public function view(): ViewRenderer
    {
        return $this->di()->getViewRenderer();
    }

    /**
     * Get the callback manager
     *
     * @return CallbackManager
     */
    public function callback(): CallbackManager
    {
        return $this->xContainer->callback();
    }
}
