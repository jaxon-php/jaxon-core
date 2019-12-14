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

abstract class Plugin implements Code\Contracts\Generator
{
    use \Jaxon\Features\Config;

    /**
     * Check if the assets of this plugin shall be included in Jaxon generated code.
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

    /**
     * Get a unique name to identify the plugin.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * @inheritDoc
     */
    public final function readyEnabled()
    {
        // For plugins, the getReadyScript() is always included in the generated code.
        return true;
    }

    /**
     * @inheritDoc
     */
    public function readyInlined()
    {
        // For plugins, the getReadyScript() can be exported to external files.
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getHash()
    {
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
     * @inheritDoc
     */
    public function getReadyScript()
    {
        return '';
    }
}
