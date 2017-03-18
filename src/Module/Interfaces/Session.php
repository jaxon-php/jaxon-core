<?php

namespace Jaxon\Module\Interfaces;

interface Session
{
    /**
     * Save data in the session
     *
     * @param string        $sKey                The session key
     * @param string        $xValue              The session value
     * 
     * @return void
     */
    public function set($sKey, $xValue);

    /**
     * Save data in the session, that will be available only until the next call
     *
     * @param string        $sKey                The session key
     * @param string        $xValue              The session value
     * 
     * @return void
     */
    // public function flash($sKey, $xValue);

    /**
     * Check if a session key exists
     *
     * @param string        $sKey                The session key
     * 
     * @return bool             True if the session key exists, else false
     */
    public function has($sKey);

    /**
     * Get data from the session
     *
     * @param string        $sKey                The session key
     * @param string        $xDefault            The default value
     * 
     * @return mixed|$xDefault             The data under the session key, or the $xDefault parameter
     */
    public function get($sKey, $xDefault = null);
}
