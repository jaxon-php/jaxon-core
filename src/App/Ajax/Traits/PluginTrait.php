<?php

/**
 * PluginTrait.php
 *
 * Plugin registration and code generation.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Thierry Feuzeu
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax\Traits;

use Jaxon\App\Ajax\Bootstrap;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AbstractPackage;
use Jaxon\Plugin\Code\CodeGenerator;
use Jaxon\Plugin\Manager\PackageManager;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\ResponsePluginInterface;
use Jaxon\Utils\Http\UriException;

trait PluginTrait
{
    /**
     * @return PluginManager
     */
    abstract public function getPluginManager(): PluginManager;

    /**
     * @return PackageManager
     */
    abstract public function getPackageManager(): PackageManager;

    /**
     * @return CodeGenerator
     */
    abstract public function getCodeGenerator(): CodeGenerator;

    /**
     * @return Bootstrap
     */
    abstract protected function getBootstrap(): Bootstrap;

    /**
     * Register request handlers, including functions, callable classes and directories.
     *
     * @param string $sType    The type of request handler being registered
     *        Options include:
     *        - Jaxon::CALLABLE_FUNCTION: a function declared at global scope
     *        - Jaxon::CALLABLE_CLASS: a class who's methods are to be registered
     *        - Jaxon::CALLABLE_DIR: a directory containing classes to be registered
     * @param string $sName
     *        When registering a function, this is the name of the function
     *        When registering a callable class, this is the class name
     *        When registering a callable directory, this is the full path to the directory
     * @param array|string $xOptions    The related options
     *
     * @return void
     * @throws SetupException
     */
    public function register(string $sType, string $sName, $xOptions = []): void
    {
        $this->getPluginManager()->registerCallable($sType, $sName, $xOptions);
    }

    /**
     * Register a plugin
     *
     * Below is a table for priorities and their description:
     * - 0 to 999: Plugins that are part of or extensions to the jaxon core
     * - 1000 to 8999: User created plugins, typically, these plugins don't care about order
     * - 9000 to 9999: Plugins that generally need to be last or near the end of the plugin list
     *
     * @param string $sClassName    The plugin class
     * @param string $sPluginName    The plugin name
     * @param integer $nPriority    The plugin priority, used to order the plugins
     *
     * @return void
     * @throws SetupException
     */
    public function registerPlugin(string $sClassName, string $sPluginName, int $nPriority = 1000)
    {
        $this->getPluginManager()->registerPlugin($sClassName, $sPluginName, $nPriority);
    }

    /**
     * Register a package
     *
     * @param string $sClassName    The package class
     * @param array $xPkgOptions    The user provided package options
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, array $xPkgOptions = [])
    {
        $this->getPackageManager()->registerPackage($sClassName, $xPkgOptions);
    }

    /**
     * Find a response plugin by name or class name
     *
     * @template R of ResponsePluginInterface
     * @param class-string<R>|string $sName    The name or class of the plugin
     *
     * @return ($sName is class-string ? R : ResponsePluginInterface)|null
     */
    public function plugin(string $sName): ResponsePluginInterface|null
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
        return $this->getPackageManager()->getPackage($sClassName);
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string
     */
    public function getJs(): string
    {
        return $this->getCodeGenerator()->getJs();
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function js(): string
    {
        return $this->getCodeGenerator()->getJs();
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string
     */
    public function getCss(): string
    {
        return $this->getCodeGenerator()->getCss();
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string
     */
    public function css(): string
    {
        return $this->getCodeGenerator()->getCss();
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
        return $this->getCodeGenerator()->getScript($bIncludeJs, $bIncludeCss);
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
        return $this->getCodeGenerator()->getScript($bIncludeJs, $bIncludeCss);
    }
}
