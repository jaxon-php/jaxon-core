<?php

namespace Jaxon\Container\Traits;

use Jaxon\Contracts\Session as SessionContract;
use Jaxon\Utils\Session\Manager as SessionManager;

use Closure;

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
        $this->set(SessionContract::class, function() {
            return new SessionManager();
        });
    }

    /**
     * Get the session manager
     *
     * @return SessionContract
     */
    public function getSessionManager()
    {
        return $this->g(SessionContract::class);
    }

    /**
     * Set the session manager
     *
     * @param Closure      $xClosure      A closure to create the session manager instance
     *
     * @return void
     */
    public function setSessionManager(Closure $xClosure)
    {
        $this->set(SessionContract::class, $xClosure);
    }
}
