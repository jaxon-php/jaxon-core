<?php

/**
 * AppInterface.php
 *
 * The Jaxon app functions.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App;

use Jaxon\Plugin\Package;
use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Request\Factory\Factory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Response\ResponseInterface;
use Jaxon\Utils\Http\UriException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Closure;

interface AppInterface
{
    /**
     * Set the logger.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * Set the value of a config option
     *
     * @param string $sName    The option name
     * @param mixed $sValue    The option value
     *
     * @return void
     */
    public function setOption(string $sName, $sValue);

    /**
     * Get the value of a config option
     *
     * @param string $sName    The option name
     * @param mixed|null $xDefault    The default value, to be returned if the option is not defined
     *
     * @return mixed
     */
    public function getOption(string $sName, $xDefault = null);

    /**
     * Check the presence of a config option
     *
     * @param string $sName    The option name
     *
     * @return bool
     */
    public function hasOption(string $sName): bool;

    /**
     * Get the configured character encoding
     *
     * @return string
     */
    public function getCharacterEncoding(): string;

    /**
     * Get the content type of the HTTP response
     *
     * @return string
     */
    public function getContentType(): string;

    /**
     * @return Factory
     */
    public function factory(): Factory;

    /**
     * Get a request to a registered class
     *
     * @param string $sClassName The class name
     *
     * @return RequestFactory|null
     */
    public function request(string $sClassName = ''): ?RequestFactory;

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string
     */
    public function getJs(): string;

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function js(): string;

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string
     */
    public function getCss(): string;

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string  the javascript code
     */
    public function css(): string;

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
     */
    public function getScript(bool $bIncludeJs = false, bool $bIncludeCss = false): string;

    /**
     * Returns the js header and wrapper code to be printed into the page
     *
     * @param bool $bIncludeJs    Also get the JS files
     * @param bool $bIncludeCss    Also get the CSS files
     *
     * @return string  the javascript code
     * @throws UriException
     */
    public function script(bool $bIncludeJs = false, bool $bIncludeCss = false): string;

    /**
     * Determine if a call is a jaxon request or a page load request
     *
     * @return bool
     */
    public function canProcessRequest(): bool;

    /**
     * Process an incoming Jaxon request, and return the response.
     *
     * @return void
     */
    public function processRequest();

    /**
     * Get the Jaxon ajax response
     *
     * @return ResponseInterface
     */
    public function ajaxResponse(): ResponseInterface;

    /**
     * Get the HTTP response
     *
     * @param string $sCode    The HTTP response code
     *
     * @return mixed
     */
    public function httpResponse(string $sCode = '200');

    /**
     * Get a registered response plugin
     *
     * @param string $sName    The name of the plugin
     *
     * @return ResponsePlugin|null
     */
    public function plugin(string $sName): ?ResponsePlugin;

    /**
     * Get a package instance
     *
     * @param string $sClassName    The package class name
     *
     * @return Package|null
     */
    public function package(string $sClassName): ?Package;

    /**
     * @return CallbackManager
     */
    public function callback(): CallbackManager;

    /**
     * @param Closure $xClosure    A closure to create the session manager instance
     *
     * @return void
     */
    public function setSessionManager(Closure $xClosure);

    /**
     * Set the container provided by the integrated framework
     *
     * @param ContainerInterface $xContainer    The container implementation
     *
     * @return void
     */
    public function setContainer(ContainerInterface $xContainer);

    /**
     * Add a view renderer with an id
     *
     * @param string $sRenderer    The renderer name
     * @param string $sExtension    The extension to append to template names
     * @param Closure $xClosure    A closure to create the view instance
     *
     * @return void
     */
    public function addViewRenderer(string $sRenderer, string $sExtension, Closure $xClosure);

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
    public function asset(bool $bExport, bool $bMinify, string $sUri = '', string $sDir = '');

    /**
     * Read config options from a config file and set up the library
     *
     * @param string $sConfigFile    The full path to the config file
     *
     * @return void
     */
    public function setup(string $sConfigFile);
}
