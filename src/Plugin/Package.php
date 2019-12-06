<?php

namespace Jaxon\Plugin;

abstract class Package
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
    abstract public static function config();

    /**
     * Get the HTML tags to include CSS code and files into the page
     *
     * The code must be enclosed in the appropriate HTML tags.
     *
     * @return string
     */
    public function css()
    {
        return '';
    }

    /**
     * Get the HTML tags to include javascript code and files into the page
     *
     * The code must be enclosed in the appropriate HTML tags.
     *
     * @return string
     */
    public function js()
    {
        return '';
    }

    /**
     * Get the javascript code to execute after page load
     *
     * The code must NOT be enclosed in HTML tags.
     *
     * @return string
     */
    public function ready()
    {
        return '';
    }

    /**
     * Get the HTML code of the package home page
     *
     * @return string
     */
    public function html()
    {
        return '';
    }
}
