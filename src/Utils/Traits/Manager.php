<?php

/**
 * Container.php - Trait for Utils classes
 *
 * Make functions of the utils classes available to Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Traits;

use Jaxon\Utils\Container;
use Jaxon\Utils\Interfaces\EventListener;

trait Manager
{
    /**
     * Get the plugin manager
     *
     * @return object        The plugin manager
     */
    public function getPluginManager()
    {
        return Container::getInstance()->getPluginManager();
    }

    /**
     * Get the request manager
     *
     * @return object        The request manager
     */
    public function getRequestManager()
    {
        return Container::getInstance()->getRequestManager();
    }

    /**
     * Get the response manager
     *
     * @return object        The response manager
     */
    public function getResponseManager()
    {
        return Container::getInstance()->getResponseManager();
    }

    /**
     * Register an event listener.
     *
     * @return void
     */
    public function addEventListener(EventListener $xEventListener)
    {
        Container::getInstance()->getEventDispatcher()->addSubscriber($xEventListener);
    }
}
