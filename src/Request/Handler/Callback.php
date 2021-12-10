<?php

/**
 * Callbacks.php - Jaxon request callbacks
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2017 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Handler;

class Callback
{
    /**
     * The callbacks to run after booting the library
     *
     * @var callable[]
     */
    protected $xBootCallbacks = [];

    /**
     * The callbacks to run before processing the request
     *
     * @var callable[]
     */
    protected $xBeforeCallbacks = [];

    /**
     * The callbacks to run afteer processing the request
     *
     * @var callable[]
     */
    protected $xAfterCallbacks = [];

    /**
     * The callbacks to run in case of invalid request
     *
     * @var callable[]
     */
    protected $xInvalidCallbacks = [];

    /**
     * The callbacks to run in case of error
     *
     * @var callable[]
     */
    protected $xErrorCallbacks = [];

    /**
     * The callbacks to run when a class is instanced
     *
     * @var callable[]
     */
    protected $xInitCallbacks = [];

    /**
     * Get the library booting callbacks.
     *
     * @return callable[]
     */
    public function getBootCallbacks()
    {
        return $this->xBootCallbacks;
    }

    /**
     * Get the pre-request processing callbacks.
     *
     * @return callable[]
     */
    public function getBeforeCallbacks()
    {
        return $this->xBeforeCallbacks;
    }

    /**
     * Get the post-request processing callbacks.
     *
     * @return callable[]
     */
    public function getAfterCallbacks()
    {
        return $this->xAfterCallbacks;
    }

    /**
     * Get the invalid request callbacks.
     *
     * @return callable[]
     */
    public function getInvalidCallbacks()
    {
        return $this->xInvalidCallbacks;
    }

    /**
     * Get the processing error callbacks.
     *
     * @return callable[]
     */
    public function getErrorCallbacks()
    {
        return $this->xErrorCallbacks;
    }

    /**
     * Get the class initialisation callbacks.
     *
     * @return callable[]
     */
    public function getInitCallbacks()
    {
        return $this->xInitCallbacks;
    }

    /**
     * Add a library booting callback.
     *
     * @param callable  $xCallable               The callback function
     *
     * @return Callback
     */
    public function boot($xCallable)
    {
        $this->xBootCallbacks[] = $xCallable;
        return $this;
    }

    /**
     * Add a pre-request processing callback.
     *
     * @param callable  $xCallable               The callback function
     *
     * @return Callback
     */
    public function before($xCallable)
    {
        $this->xBeforeCallbacks[] = $xCallable;
        return $this;
    }

    /**
     * Add a post-request processing callback.
     *
     * @param callable  $xCallable               The callback function
     *
     * @return Callback
     */
    public function after($xCallable)
    {
        $this->xAfterCallbacks[] = $xCallable;
        return $this;
    }

    /**
     * Add a invalid request callback.
     *
     * @param callable  $xCallable               The callback function
     *
     * @return Callback
     */
    public function invalid($xCallable)
    {
        $this->xInvalidCallbacks[] = $xCallable;
        return $this;
    }

    /**
     * Add a processing error callback.
     *
     * @param callable  $xCallable               The callback function
     *
     * @return Callback
     */
    public function error($xCallable)
    {
        $this->xErrorCallbacks[] = $xCallable;
        return $this;
    }

    /**
     * Add a class initialisation callback.
     *
     * @param callable  $xCallable               The callback function
     *
     * @return Callback
     */
    public function init($xCallable)
    {
        $this->xInitCallbacks[] = $xCallable;
        return $this;
    }
}
