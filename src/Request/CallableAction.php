<?php

/**
 * CallableAction.php
 *
 * This class contains the name of the function or class and method targetted by a Jaxon request.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request;

abstract class CallableAction
{
    /**
     * @var array
     */
    protected array $aActionArgs;

    /**
     * Check if the request action is Function.
     *
     * @return bool
     */
    abstract public function isFunction(): bool;

    /**
     * Check if the request action is Class.
     *
     * @return bool
     */
    abstract public function isClass(): bool;

    /**
     * The callable function name.
     *
     * @return string
     */
    abstract public function getFunctionName(): string;

    /**
     * The callable class name.
     *
     * @return string
     */
    abstract public function getClassName(): string;

    /**
     * The callable method name.
     *
     * @return string
     */
    abstract public function getMethodName(): string;

    /**
     * The request action args.
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->aActionArgs;
    }

    /**
     * The request action name.
     *
     * @return string
     */
    abstract public function func(): string;

    /**
     * The request action args.
     *
     * @return array
     */
    public function args(): array
    {
        return $this->aActionArgs;
    }
}
