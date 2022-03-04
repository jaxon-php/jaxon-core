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

namespace Jaxon\Features;

use Jaxon\Contracts\Event\Listener as EventListener;

use function jaxon;

trait Event
{
    /**
     * Register an event listener.
     *
     * @return void
     */
    public function addEventListener(EventListener $xEventListener)
    {
        jaxon()->di()->getEventDispatcher()->addSubscriber($xEventListener);
    }

    /**
     * Trigger an event.
     *
     * @param string        $sEvent            The event name
     *
     * @return void
     */
    public function triggerEvent(string $sEvent)
    {
        jaxon()->di()->getEventDispatcher()->dispatch($sEvent);
    }

    /**
     * Return an array of events to listen to.
     *
     * This is the default implementation on the EventListener interface.
     *
     * @return array
     */
    public function getEvents(): array
    {
        return [];
    }

    /**
     * Return an array of events to listen to.
     *
     * This is the implementation of the Lemon\Event\EventSubscriberInterface interface.
     *
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return $this->getEvents();
    }
}
