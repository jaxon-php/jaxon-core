<?php

/**
 * Ajax.php
 *
 * The Jaxon library.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\ClassContainer;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\AbstractPackage;
use Jaxon\Plugin\ResponsePluginInterface;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Response\ResponseManager;
use Jaxon\Utils\Http\UriException;
use Psr\Log\LoggerInterface;

use function trim;

trait LibTrait
{
    /**
     * @var Container
     */
    protected $xContainer = null;

    /**
     * @var ClassContainer
     */
    protected $xClassContainer = null;

    /**
     * @var ConfigManager
     */
    protected $xConfigManager = null;

    /**
     * @var ResponseManager
     */
    protected $xResponseManager = null;

    /**
     * @var PluginManager
     */
    protected $xPluginManager = null;

    /**
     * @var Translator
     */
    protected $xTranslator = null;

    /**
     * @return Container
     */
    public function di(): ?Container
    {
        return $this->xContainer;
    }

    /**
     * @return ClassContainer
     */
    public function cls(): ?ClassContainer
    {
        return $this->xClassContainer;
    }

    /**
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->di()->getLogger();
    }

    /**
     * @return Translator
     */
    public function translator(): Translator
    {
        return $this->xTranslator ?: $this->xTranslator = $this->di()->g(Translator::class);
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager(): ConfigManager
    {
        return $this->xConfigManager ?:
            $this->xConfigManager = $this->xContainer->g(ConfigManager::class);
    }

    /**
     * @return ResponseManager
     */
    protected function getResponseManager(): ResponseManager
    {
        return $this->xResponseManager ?:
            $this->xResponseManager = $this->xContainer->g(ResponseManager::class);
    }

    /**
     * @return PluginManager
     */
    protected function getPluginManager(): PluginManager
    {
        return $this->xPluginManager ?:
            $this->xPluginManager = $this->xContainer->g(PluginManager::class);
    }

    /**
     * Get the configured character encoding
     *
     * @return string
     */
    public function getCharacterEncoding(): string
    {
        return trim($this->getOption('core.encoding', ''));
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

    /**
     * Get an instance of a registered class
     *
     * @template T
     * @param class-string<T> $sClassName the class name
     *
     * @return T|null
     * @throws SetupException
     */
    public function cl(string $sClassName): mixed
    {
        return $this->cls()->makeRegisteredObject($sClassName);
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string
     */
    public function getJs(): string
    {
        return $this->di()->getCodeGenerator()->getJs();
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function js(): string
    {
        return $this->di()->getCodeGenerator()->getJs();
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string
     */
    public function getCss(): string
    {
        return $this->di()->getCodeGenerator()->getCss();
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string
     */
    public function css(): string
    {
        return $this->di()->getCodeGenerator()->getCss();
    }

    /**
     * Returns the js header and wrapper code to be printed into the page
     *
     * The javascript code returned by this function depends on the plugins
     * that are included and the functions and classes that are registered.
     *
     * @param bool $bIncludeJs    Also get the js code
     * @param bool $bIncludeCss    Also get the css code
     *
     * @return string
     * @throws UriException
     */
    public function getScript(bool $bIncludeJs = false, bool $bIncludeCss = false): string
    {
        return $this->di()->getCodeGenerator()->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Returns the js header and wrapper code to be printed into the page
     *
     * @param bool $bIncludeJs    Also get the js code
     * @param bool $bIncludeCss    Also get the css code
     *
     * @return string  the javascript code
     * @throws UriException
     */
    public function script(bool $bIncludeJs = false, bool $bIncludeCss = false): string
    {
        return $this->di()->getCodeGenerator()->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Determine if a call is a jaxon request or a page load request
     *
     * @return bool
     */
    public function canProcessRequest(): bool
    {
        return $this->di()->getRequestHandler()->canProcessRequest();
    }

    /**
     * Find the specified response plugin by name or class name
     *
     * @template R of ResponsePluginInterface
     * @param string|class-string<R> $sName    The name of the plugin
     *
     * @return ($sName is class-string ? R : ResponsePluginInterface)|null
     */
    public function plugin(string $sName): ?ResponsePluginInterface
    {
        return $this->getPluginManager()->getResponsePlugin($sName);
    }

    /**
     * Get a package instance
     *
     * @template P of AbstractPackage
     * @param class-string<P> $sClassName The package class name
     *
     * @return P|null
     */
    public function package(string $sClassName): ?AbstractPackage
    {
        return $this->di()->getPackageManager()->getPackage($sClassName);
    }

    /**
     * Get the callback manager
     *
     * @return CallbackManager
     */
    public function callback(): CallbackManager
    {
        return $this->di()->getCallbackManager();
    }
}
