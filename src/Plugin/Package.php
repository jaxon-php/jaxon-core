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

abstract class Package implements Code\Contracts\Generator
{
    /**
     * The configuration options of the package
     *
     * @var array
     */
    protected $aOptions = [];

    /**
     * Indicate if the plugin ready code shall be executed
     *
     * @var boolean
     */
    protected $bRunJsReady = false;

    /**
     * Get package options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->aOptions;
    }

    /**
     * Get or set the $bRunJsReady property
     *
     * @param null|boolean  $bRunJsReady     If not null, the value to set
     *
     * @return boolean
     */
    public function start($bRunJsReady = null)
    {
        if($bRunJsReady !== null)
        {
            $this->bRunJsReady = $bRunJsReady;
        }
        return $this->bRunJsReady;
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
    public final function getScript()
    {
        // Packages do not generate script. So we make this method final.
        return '';
    }

    /**
     * Get the HTML code of the package home page
     *
     * @return string
     */
    abstract public function getHtml();
}
