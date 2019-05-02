<?php

/**
 * Container.php - Trait for Utils classes
 *
 * Make functions of the utils classes available to Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Features;

use Jaxon\DI\Container;

trait Manager
{
    /**
     * Get the plugin manager
     *
     * @return Jaxon\Plugin\Manager
     */
    public function getPluginManager()
    {
        return Container::getInstance()->getPluginManager();
    }

    /**
     * Get the request manager
     *
     * @return Jaxon\Request\Handler
     */
    public function getRequestHandler()
    {
        return Container::getInstance()->getRequestHandler();
    }

    /**
     * Get the response manager
     *
     * @return Jaxon\Response\Manager
     */
    public function getResponseManager()
    {
        return Container::getInstance()->getResponseManager();
    }
}
