<?php

/**
 * AbstractPackage.php
 *
 * Base class for the Jaxon packages.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin;

use Jaxon\App\View\ViewRenderer;
use Jaxon\Config\Config;

abstract class AbstractPackage extends AbstractCodeGenerator
{
    /**
     * The configuration options of the package
     *
     * @var Config
     */
    protected $xPkgConfig;

    /**
     * The view renderer
     *
     * @var ViewRenderer
     */
    protected $xRenderer;

    /**
     * Get the path to the config file, or the config options in an array.
     *
     * @return string|array
     */
    abstract public static function config();

    /**
     * Get the package config object
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->xPkgConfig;
    }

    /**
     * This method is automatically called after the package instance is created and configured.
     *
     * @return void
     */
    protected function init()
    {}

    /**
     * Get the value of a given package option
     *
     * @param string $sOption    The option name
     * @param mixed $xDefault    The default value
     *
     * @return mixed
     */
    public function getOption(string $sOption, $xDefault = null)
    {
        return $this->xPkgConfig->getOption($sOption, $xDefault);
    }

    /**
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    public function view(): ViewRenderer
    {
        return $this->xRenderer;
    }

    /**
     * @inheritDoc
     */
    final public function getHash(): string
    {
        // Packages do not generate hash on their own. So we make this method final.
        return '';
    }

    /**
     * Get the HTML code of the package home page
     *
     * @return string
     */
    abstract public function getHtml(): string;

    /**
     * Get the HTML code of the package home page
     *
     * This method is an alias for getHtml().
     *
     * @return string
     */
    public function html(): string
    {
        return $this->getHtml();
    }
}
