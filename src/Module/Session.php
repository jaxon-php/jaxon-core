<?php

namespace Jaxon\Module;

class Session
{
    /**
     * Get the current session id
     * 
     * @return string           The session id
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Generate a new session id
     * 
     * @param bool          $bDeleteData         Whether to delete data from the previous session
     * 
     * @return void
     */
    public function newId($bDeleteData = false)
    {
        session_regenerate_id($bDeleteData);
    }

    /**
     * Save data in the session
     *
     * @param string        $sKey                The session key
     * @param string        $xValue              The session value
     * 
     * @return void
     */
    public function set($sKey, $xValue)
    {
        $_SESSION[$sKey] = $xValue;
    }

    /**
     * Check if a session key exists
     *
     * @param string        $sKey                The session key
     * 
     * @return bool             True if the session key exists, else false
     */
    public function has($sKey)
    {
        return key_exists($sKey, $_SESSION);
    }

    /**
     * Get data from the session
     *
     * @param string        $sKey                The session key
     * @param string        $xDefault            The default value
     * 
     * @return mixed|$xDefault             The data under the session key, or the $xDefault parameter
     */
    public function get($sKey, $xDefault = null)
    {
        return $this->has($sKey) ? $_SESSION[$sKey] : $xDefault;
    }

    /**
     * Get all data in the session
     * 
     * @return array             An array of all data in the session
     */
    public function all()
    {
        return $_SESSION;
    }

    /**
     * Delete a session key and its data
     *
     * @param string        $sKey                The session key
     * 
     * @return void
     */
    public function delete($sKey)
    {
        if(key_exists($sKey, $_SESSION))
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
        $_SESSION = [];
    }
}
