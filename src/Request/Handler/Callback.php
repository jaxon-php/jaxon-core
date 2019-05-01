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
     * @var Callable
     */
    protected $xBeforeCallback = null;

    /**
     * The callback to run afteer processing the request
     *
     * @var Callable
     */
    protected $xAfterCallback = null;

    /**
     * The callback to run in case of invalid request
     *
     * @var Callable
     */
    protected $xInvalidCallback = null;

    /**
     * The callback to run in case of error
     *
     * @var Callable
     */
    protected $xErrorCallback = null;

    /**
     * Get or set the pre-request processing callback.
     *
     * @param Callable|null  $xCallable               The callback function
     *
     * @return void
     */
    public function before($xCallable = null)
    {
        if($xCallable === null)
        {
            return $this->xBeforeCallback;
        }
        $this->xBeforeCallback = $xCallable;
    }

    /**
     * Get or set the post-request processing callback.
     *
     * @param Callable|null  $xCallable               The callback function
     *
     * @return void
     */
    public function after($xCallable = null)
    {
        if($xCallable === null)
        {
            return $this->xAfterCallback;
        }
        $this->xAfterCallback = $xCallable;
    }

    /**
     * Get or set the invalid request callback.
     *
     * @param Callable|null  $xCallable               The callback function
     *
     * @return void
     */
    public function invalid($xCallable = null)
    {
        if($xCallable === null)
        {
            return $this->xInvalidCallback;
        }
        $this->xInvalidCallback = $xCallable;
    }

    /**
     * Get or set the processing error callback.
     *
     * @param Callable|null  $xCallable               The callback function
     *
     * @return void
     */
    public function error($xCallable = null)
    {
        if($xCallable === null)
        {
            return $this->xErrorCallback;
        }
        $this->xErrorCallback = $xCallable;
    }
}
