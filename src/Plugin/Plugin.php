<?php

/**
 * Plugin.php - Plugin interface
 *
 * Generic interface for all Jaxon plugins.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin;

abstract class Plugin
{
    use \Jaxon\Utils\Traits\Config;

    /**
     * Generate the javascript code for this plugin
     *
     * Called by <Jaxon\Plugin\Manager> when the page's HTML is being sent to the browser.
     * This code is either inserted right into the HTML code, or exported in an external file
     * which is then included in the page.
     *
     * @return string
     */
    abstract public function getScript();

    /**
     * Generate a unique hash for this plugin
     *
     * @return string
     */
    abstract public function generateHash();

    /**
     * Return true if the object is a request plugin. Always return false here.
     *
     * @return boolean
     */
    public function isRequest()
    {
        return false;
    }

    /**
     * Return true if the object is a response plugin. Always return false here.
     *
     * @return boolean
     */
    public function isResponse()
    {
        return false;
    }

    /**
     * Get the plugin name
     *
     * Called by the <Jaxon\Plugin\Manager> when the user script requests a plugin.
     * This name must match the plugin name requested in the called to <Jaxon\Response\Response->plugin>.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Check if the assets of this plugin shall be included in Jaxon generated code
     *
     * @return boolean
     */
    protected function includeAssets()
    {
        $sPluginOptionName = 'assets.include.' . $this->getName();
        if($this->hasOption($sPluginOptionName) && !$this->getOption($sPluginOptionName))
        {
            return false;
        }
        if($this->hasOption('assets.include.all') && !$this->getOption('assets.include.all'))
        {
            return false;
        }
        return true;
    }
}
