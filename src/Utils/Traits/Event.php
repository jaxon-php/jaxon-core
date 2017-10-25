<?php

/**
 * EventListener.php - Trait for event listener and dispatcher.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Traits;

use Jaxon\Utils\Container;
use Jaxon\Utils\Interfaces\EventListener;

trait Event
{
    /**
     * Register an event listener.
     *
     * @return void
     */
    public function addEventListener(EventListener $xEventListener)
    {
        Container::getInstance()->getEventDispatcher()->addSubscriber($xEventListener);
    }

    /**
     * Trigger an event.
     *
     * @param string        $sEvent            The event name
     *
     * @return void
     */
    public function triggerEvent($sEvent)
    {
        Container::getInstance()->getEventDispatcher()->dispatch($sEvent);
    }

    /**
     * Return an array of events to listen to.
     *
     * This is the default implementation on the EventListener interface.
     *
     * @return array An empty array
     */
    public function getEvents()
    {
        return [];
    }

    /**
     * Return an array of events to listen to.
     *
     * This is the implementation of the Lemon\Event\EventSubscriberInterface interface.
     *
     * @return array The event names to listen to
     */
    public function getSubscribedEvents()
    {
        return $this->getEvents();
    }
}
