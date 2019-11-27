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
     * The callback to run before processing the request
     *
     * @var callable
     */
    protected $xBeforeCallback = null;

    /**
     * The callback to run afteer processing the request
     *
     * @var callable
     */
    protected $xAfterCallback = null;

    /**
     * The callback to run in case of invalid request
     *
     * @var callable
     */
    protected $xInvalidCallback = null;

    /**
     * The callback to run in case of error
     *
     * @var callable
     */
    protected $xErrorCallback = null;

    /**
     * The callback to run when a class is instanciated
     *
     * @var callable
     */
    protected $xInitCallback = null;

    /**
     * Get or set the pre-request processing callback.
     *
     * @param callable|null  $xCallable               The callback function
     *
     * @return Callback|callable
     */
    public function before($xCallable = null)
    {
        if($xCallable === null)
        {
            return $this->xBeforeCallback;
        }
        $this->xBeforeCallback = $xCallable;
        return $this;
    }

    /**
     * Get or set the post-request processing callback.
     *
     * @param callable|null  $xCallable               The callback function
     *
     * @return Callback|callable
     */
    public function after($xCallable = null)
    {
        if($xCallable === null)
        {
            return $this->xAfterCallback;
        }
        $this->xAfterCallback = $xCallable;
        return $this;
    }

    /**
     * Get or set the invalid request callback.
     *
     * @param callable|null  $xCallable               The callback function
     *
     * @return Callback|callable
     */
    public function invalid($xCallable = null)
    {
        if($xCallable === null)
        {
            return $this->xInvalidCallback;
        }
        $this->xInvalidCallback = $xCallable;
        return $this;
    }

    /**
     * Get or set the processing error callback.
     *
     * @param callable|null  $xCallable               The callback function
     *
     * @return Callback|callable
     */
    public function error($xCallable = null)
    {
        if($xCallable === null)
        {
            return $this->xErrorCallback;
        }
        $this->xErrorCallback = $xCallable;
        return $this;
    }

    /**
     * Get or set the class initialisation callback.
     *
     * @param callable|null  $xCallable               The callback function
     *
     * @return Callback|callable
     */
    public function init($xCallable = null)
    {
        if($xCallable === null)
        {
            return $this->xInitCallback;
        }
        $this->xInitCallback = $xCallable;
        return $this;
    }
}
