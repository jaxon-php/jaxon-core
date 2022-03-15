<?php

namespace Jaxon\Container\Traits;

use Closure;
use Jaxon\Session\SessionInterface;
use Jaxon\Session\SessionManager;

trait SessionTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerSessions()
    {
        // Set the default session manager
        $this->set(SessionInterface::class, function() {
            return new SessionManager();
        });
    }

    /**
     * Get the session manager
     *
     * @return SessionInterface
     */
    public function getSessionManager(): SessionInterface
    {
        return $this->g(SessionInterface::class);
    }

    /**
     * Set the session manager
     *
     * @param Closure $xClosure    A closure to create the session manager instance
     *
     * @return void
     */
    public function setSessionManager(Closure $xClosure)
    {
        $this->set(SessionInterface::class, $xClosure);
    }
}
