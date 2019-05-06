<?php

/**
 * Autoload.php - Upload Trait
 *
 * The Jaxon class uses a modular plug-in system to facilitate the processing
 * of special Ajax requests made by a PHP page.
 * It generates Javascript that the page must include in order to make requests.
 * It handles the output of response commands (see <Jaxon\Response\Response>).
 * Many flags and settings can be adjusted to effect the behavior of the Jaxon class
 * as well as the client-side javascript.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2017 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Features;

trait Autoload
{
    /**
     * Get the DI container
     *
     * @return \Jaxon\DI\Container
     */
    abstract public function di();

    /**
     * Set Jaxon to use the Composer autoloader
     *
     * @return void
     */
    public function useComposerAutoloader()
    {
        // The CallableDir plugin
        $xPlugin = $this->di()->getPluginManager()->getRequestPlugin(self::CALLABLE_DIR);
        $xPlugin->useComposerAutoloader();
    }

    /**
     * Disable Jaxon classes autoloading
     *
     * @return void
     */
    public function disableAutoload()
    {
        // The CallableDir plugin
        $xPlugin = $this->di()->getPluginManager()->getRequestPlugin(self::CALLABLE_DIR);
        $xPlugin->disableAutoload();
    }
}
