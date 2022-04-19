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

namespace Jaxon\App\Traits;

use Closure;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\Package;
use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Request\Factory\Factory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Response\ResponseInterface;
use Jaxon\Utils\Http\UriException;
use Psr\Log\LoggerInterface;

use function trim;

trait AjaxTrait
{
    /**
     * @var Container
     */
    protected $xContainer = null;

    /**
     * @var ConfigManager
     */
    protected $xConfigManager;

    /**
     * @var ResponseManager
     */
    protected $xResponseManager;

    /**
     * @var PluginManager
     */
    protected $xPluginManager;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * @return Container
     */
    public function di(): ?Container
    {
        return $this->xContainer;
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
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->di()->getLogger();
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
     * @return Translator
     */
    public function translator(): Translator
    {
        return $this->xTranslator;
    }

    /**
     * Set the value of a config option
     *
     * @param string $sName    The option name
     * @param mixed $sValue    The option value
     *
     * @return void
     */
    public function setOption(string $sName, $sValue)
    {
        $this->xConfigManager->setOption($sName, $sValue);
    }

    /**
     * Get the value of a config option
     *
     * @param string $sName    The option name
     * @param mixed|null $xDefault    The default value, to be returned if the option is not defined
     *
     * @return mixed
     */
    public function getOption(string $sName, $xDefault = null)
    {
        return $this->xConfigManager->getOption($sName, $xDefault);
    }

    /**
     * Check the presence of a config option
     *
     * @param string $sName    The option name
     *
     * @return bool
     */
    public function hasOption(string $sName): bool
    {
        return $this->xConfigManager->hasOption($sName);
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
        return $this->xResponseManager->getContentType();
    }

    /**
     * @return Factory
     */
    public function factory(): Factory
    {
        return $this->di()->getFactory();
    }

    /**
     * Get a request to a registered class
     *
     * @param string $sClassName The class name
     *
     * @return RequestFactory|null
     * @throws SetupException
     */
    public function request(string $sClassName = ''): ?RequestFactory
    {
        return $this->factory()->request($sClassName);
    }

    /**
     * Get the global Response object
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->xResponseManager->getResponse();
    }

    /**
     * Return the javascript header code and file includes
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
        return $this->getJs();
    }

    /**
     * Return the CSS header code and file includes
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
     * @return string  the javascript code
     */
    public function css(): string
    {
        return $this->getCss();
    }

    /**
     * Returns the js header and wrapper code to be printed into the page
     *
     * The javascript code returned by this function is dependent on the plugins
     * that are included and the functions and classes that are registered.
     *
     * @param bool $bIncludeJs    Also get the JS files
     * @param bool $bIncludeCss    Also get the CSS files
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
     * @param bool $bIncludeJs    Also get the JS files
     * @param bool $bIncludeCss    Also get the CSS files
     *
     * @return string  the javascript code
     * @throws UriException
     */
    public function script(bool $bIncludeJs = false, bool $bIncludeCss = false): string
    {
        return $this->getScript($bIncludeJs, $bIncludeCss);
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
     * Get a registered response plugin
     *
     * @param string $sName    The name of the plugin
     *
     * @return ResponsePlugin|null
     */
    public function plugin(string $sName): ?ResponsePlugin
    {
        return $this->xPluginManager->getResponsePlugin($sName);
    }

    /**
     * Get a package instance
     *
     * @param string $sClassName    The package class name
     *
     * @return Package|null
     */
    public function package(string $sClassName): ?Package
    {
        return $this->di()->getPackageManager()->getPackage($sClassName);
    }

    /**
     * @return CallbackManager
     */
    public function callback(): CallbackManager
    {
        return $this->di()->getCallbackManager();
    }

    /**
     * @param Closure $xClosure    A closure to create the session manager instance
     *
     * @return void
     */
    public function setSessionManager(Closure $xClosure)
    {
        $this->di()->setSessionManager($xClosure);
    }
}
