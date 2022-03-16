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

abstract class Plugin implements PluginInterface, CodeGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public final function readyEnabled(): bool
    {
        // For plugins, the getReadyScript() is always included in the generated code.
        return true;
    }

    /**
     * @inheritDoc
     */
    public function readyInlined(): bool
    {
        // For plugins, the getReadyScript() can be exported to external files.
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getCss(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getJs(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getScript(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getReadyScript(): string
    {
        return '';
    }
}
