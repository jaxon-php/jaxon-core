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

use Jaxon\Exception\AppException;
use Jaxon\Exception\RequestException;
use Jaxon\Request\Target;
use Exception;

use function array_merge;
use function array_values;
use function call_user_func_array;
use function count;
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

    /**
     * @param callable $xCallback
     * @param array $aParameters
     *
     * @return void
     */
    private function executeCallback(callable $xCallback, array $aParameters): void
    {
        call_user_func_array($xCallback, $aParameters);
    }

    /**
     * @param array $aCallbacks
     * @param array $aParameters
     *
     * @return void
     */
    private function executeCallbacks(array $aCallbacks, array $aParameters): void
    {
        foreach($aCallbacks as $xCallback)
        {
            $this->executeCallback($xCallback, $aParameters);
        }
    }

    /**
     * Execute the class initialisation callbacks.
     *
     * @param mixed $xComponent
     *
     * @return void
     */
    public function onInit($xComponent): void
    {
        $this->executeCallbacks($this->aInitCallbacks, [$xComponent]);
    }

    /**
     * These are the pre-request processing callbacks passed to the Jaxon library.
     *
     * @param Target $xTarget
     * @param bool $bEndRequest If set to true, the request processing is interrupted.
     *
     * @return void
     * @throws RequestException
     */
    public function onBefore(Target $xTarget, bool &$bEndRequest): void
    {
        // Call the user defined callback
        foreach($this->aBeforeCallbacks as $xCallback)
        {
            $this->executeCallback($xCallback, [$xTarget, &$bEndRequest]);
            if($bEndRequest)
            {
                return;
            }
        }
    }

    /**
     * These are the post-request processing callbacks passed to the Jaxon library.
     *
     * @param Target $xTarget
     * @param bool $bEndRequest
     *
     * @return void
     * @throws RequestException
     */
    public function onAfter(Target $xTarget, bool $bEndRequest): void
    {
        $this->executeCallbacks($this->aAfterCallbacks, [$xTarget, $bEndRequest]);
    }

    /**
     * These callbacks are called whenever an invalid request is processed.
     *
     * @param RequestException $xException
     *
     * @return void
     * @throws RequestException
     */
    public function onInvalid(RequestException $xException): void
    {
        $this->executeCallbacks($this->aInvalidCallbacks, [$xException]);
        throw $xException;
    }

    /**
     * Get the exception callbacks.
     *
     * @param Exception $xException      The exception class
     *
     * @return callable[]
     */
    private function getExceptionCallbacks(Exception $xException): array
    {
        $aExceptionCallbacks = [];
        foreach($this->aExceptionCallbacks as $sExClass => $aCallbacks)
        {
            if(is_a($xException, $sExClass))
            {
                $aExceptionCallbacks = array_merge($aExceptionCallbacks, $aCallbacks);
            }
        }
        return array_merge(array_values($aExceptionCallbacks), $this->aErrorCallbacks);
    }

    /**
     * These callbacks are called whenever an invalid request is processed.
     *
     * @param Exception $xException
     *
     * @return void
     * @throws Exception
     */
    public function onError(Exception $xException): void
    {
        $aExceptionCallbacks = $this->getExceptionCallbacks($xException);
        if(count($aExceptionCallbacks) === 0)
        {
            // Throw the exception if no handler is found.
            throw new AppException($xException->getMessage());
        }

        $this->executeCallbacks($aExceptionCallbacks, [$xException]);
    }
}
