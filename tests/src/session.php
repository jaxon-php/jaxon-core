<?php

use Jaxon\App\Session\SessionInterface;

class SessionManager implements SessionInterface
{
    /**
     * Get the current session id
     *
     * @return string
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Generate a new session id
     *
     * @param bool $bDeleteData    Whether to delete data from the previous session
     *
     * @return void
     */
    public function newId(bool $bDeleteData = false)
    {
        session_regenerate_id($bDeleteData);
    }

    /**
     * Save data in the session
     *
     * @param string $sKey    The session key
     * @param mixed $xValue    The session value
     *
     * @return void
     */
    public function set(string $sKey, $xValue)
    {
        $_SESSION[$sKey] = $xValue;
    }

    /**
     * Save data in the session, that will be available only until the next call
     *
     * @param string $sKey    The session key
     * @param mixed $xValue    The session value
     *
     * @return void
     */
    /* public function flash(string $sKey, $xValue)
    {
    }*/

    /**
     * Check if a session key exists
     *
     * @param string $sKey    The session key
     *
     * @return bool
     */
    public function has(string $sKey): bool
    {
        return isset($_SESSION[$sKey]);
    }

    /**
     * Get data from the session
     *
     * @param string $sKey    The session key
     * @param string|null $xDefault    The default value
     *
     * @return mixed
     */
    public function get(string $sKey, $xDefault = null)
    {
        return $_SESSION[$sKey] ?? $xDefault;
    }

    /**
     * Get all data in the session
     *
     * @return array
     */
    public function all(): array
    {
        return $_SESSION;
    }

    /**
     * Delete a session key and its data
     *
     * @param string $sKey    The session key
     *
     * @return void
     */
    public function delete(string $sKey)
    {
        if(isset($_SESSION[$sKey]))
        {
            unset($_SESSION[$sKey]);
        }
    }

    /**
     * Delete all data in the session
     *
     * @return void
     */
    public function clear()
    {
        session_unset();
    }
}
