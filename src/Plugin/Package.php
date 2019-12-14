<?php

/**
 * Generator.php - Code generator interface
 *
 * Any class generating css or js code must implement this interface.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin;

use Jaxon\Utils\Config\Config;

abstract class Package implements Code\Contracts\Generator
{
    /**
     * The configuration options of the package
     *
     * @var array
     */
    protected $aOptions = [];

    /**
     * The configuration options of the package
     *
     * @var Config
     */
    protected $xConfig;

    /**
     * Whether to include the getReadyScript() in the generated code.
     *
     * @var boolean
     */
    protected $bReadyEnabled = false;

    /**
     * Get package options in an array.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->aOptions;
    }

    /**
     * Get package options in a Config object.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->xConfig;
    }

    /**
     * Get the view renderer
     *
     * @return \Jaxon\Utils\View\Renderer
     */
    public function view()
    {
        return jaxon()->view();
    }

    /**
     * Get the path to the config file
     *
     * @return string
     */
    abstract public static function getConfigFile();

    /**
     * Include the getReadyScript() in the generated code.
     *
     * @return void
     */
    public function ready()
    {
        $this->bReadyEnabled = true;
    }

    /**
     * @inheritDoc
     */
    public function readyEnabled()
    {
        return $this->bReadyEnabled;
    }

    /**
     * @inheritDoc
     */
    public final function readyInlined()
    {
        // For packages, the getReadyScript() is never exported to external files.
        return true;
    }

    /**
     * @inheritDoc
     */
    public final function getHash()
    {
        // Packages do not generate hash on their own. So we make this method final.
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getCss()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getJs()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getScript()
    {
        return '';
    }

    /**
     * Get the HTML code of the package home page
     *
     * @return string
     */
    abstract public function getHtml();
}
