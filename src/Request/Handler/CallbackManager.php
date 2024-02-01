<?php

/**
 * Callbacks.php
 *
 * Jaxon request callback manager
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2017 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Handler;

use Exception;

use function array_merge;
use function array_values;
use function is_a;

class CallbackManager
{
    /**
     * The callbacks to run after booting the library
     *
     * @var callable[]
     */
    protected $aBootCallbacks = [];

    /**
     * The callbacks to run before processing the request
     *
     * @var callable[]
     */
    protected $aBeforeCallbacks = [];

    /**
     * The callbacks to run after processing the request
     *
     * @var callable[]
     */
    protected $aAfterCallbacks = [];

    /**
     * The callbacks to run in case of invalid request
     *
     * @var callable[]
     */
    protected $aInvalidCallbacks = [];

    /**
     * The callbacks to run in case of error
     *
     * @var callable[]
     */
    protected $aErrorCallbacks = [];

    /**
     * The callbacks to run in case of exception
     *
     * @var callable[][]
     */
    protected $aExceptionCallbacks = [];

    /**
     * The callbacks to run when a class is instanced
     *
     * @var callable[]
     */
    protected $aInitCallbacks = [];

    /**
     * Get the library booting callbacks, and reset the array.
     *
     * @return callable[]
     */
    public function popBootCallbacks(): array
    {
        if(empty($this->aBootCallbacks))
        {
            return [];
        }
        $aCallbacks = $this->aBootCallbacks;
        $this->aBootCallbacks = [];
        return $aCallbacks;
    }

    /**
     * Get the pre-request processing callbacks.
     *
     * @return callable[]
     */
    public function getBeforeCallbacks(): array
    {
        return $this->aBeforeCallbacks;
    }

    /**
     * Get the post-request processing callbacks.
     *
     * @return callable[]
     */
    public function getAfterCallbacks(): array
    {
        return $this->aAfterCallbacks;
    }

    /**
     * Get the invalid request callbacks.
     *
     * @return callable[]
     */
    public function getInvalidCallbacks(): array
    {
        return $this->aInvalidCallbacks;
    }

    /**
     * Get the error callbacks.
     *
     * @return callable[]
     */
    public function getErrorCallbacks(): array
    {
        return $this->aErrorCallbacks;
    }

    /**
     * Get the exception callbacks.
     *
     * @param Exception $xException      The exception class
     *
     * @return callable[]
     */
    public function getExceptionCallbacks(Exception $xException): array
    {
        $aExceptionCallbacks = [];
        foreach($this->aExceptionCallbacks as $sExClass => $aCallbacks)
        {
            if(is_a($xException, $sExClass))
            {
                $aExceptionCallbacks = array_merge($aExceptionCallbacks, $aCallbacks);
            }
        }
        return array_values($aExceptionCallbacks);
    }

    /**
     * Get the class initialisation callbacks.
     *
     * @return callable[]
     */
    public function getInitCallbacks(): array
    {
        return $this->aInitCallbacks;
    }

    /**
     * Add a library booting callback.
     *
     * @param callable $xCallable    The callback function
     *
     * @return CallbackManager
     */
    public function boot(callable $xCallable): CallbackManager
    {
        $this->aBootCallbacks[] = $xCallable;
        return $this;
    }

    /**
     * Add a pre-request processing callback.
     *
     * @param callable $xCallable    The callback function
     *
     * @return CallbackManager
     */
    public function before(callable $xCallable): CallbackManager
    {
        $this->aBeforeCallbacks[] = $xCallable;
        return $this;
    }

    /**
     * Add a post-request processing callback.
     *
     * @param callable $xCallable    The callback function
     *
     * @return CallbackManager
     */
    public function after(callable $xCallable): CallbackManager
    {
        $this->aAfterCallbacks[] = $xCallable;
        return $this;
    }

    /**
     * Add a invalid request callback.
     *
     * @param callable $xCallable    The callback function
     *
     * @return CallbackManager
     */
    public function invalid(callable $xCallable): CallbackManager
    {
        $this->aInvalidCallbacks[] = $xCallable;
        return $this;
    }

    /**
     * Add a processing error callback.
     *
     * @param callable $xCallable   The callback function
     * @param string $sExClass      The exception class
     *
     * @return CallbackManager
     */
    public function error(callable $xCallable, string $sExClass = ''): CallbackManager
    {
        if($sExClass === '' || $sExClass === Exception::class)
        {
            $this->aErrorCallbacks[] = $xCallable;
            return $this;
        }
        // Callback for a given exception class
        if(isset($this->aExceptionCallbacks[$sExClass]))
        {
            $this->aExceptionCallbacks[$sExClass][] = $xCallable;
            return $this;
        }
        $this->aExceptionCallbacks[$sExClass] = [$xCallable];
        return $this;
    }

    /**
     * Add a class initialisation callback.
     *
     * @param callable $xCallable    The callback function
     *
     * @return CallbackManager
     */
    public function init(callable $xCallable): CallbackManager
    {
        $this->aInitCallbacks[] = $xCallable;
        return $this;
    }
}
