<?php

/**
 * CallableComponent.php
 *
 * This class contains the name of the class and method targetted by a Jaxon request.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Request\CallableComponent;

use Jaxon\Request\CallableAction;

class CallableComponent extends CallableAction
{
    /**
     * @param string $sClassName    The class name
     * @param string $sMethodName    The method name
     * @param array $aActionArgs    The method args
     */
    public function __construct(private string $sClassName,
        private string $sMethodName, array $aActionArgs)
    {
        $this->aActionArgs = $aActionArgs;
    }

    /**
     * @inheritDoc
     */
    public function isFunction(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isClass(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getFunctionName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getClassName(): string
    {
        return $this->sClassName;
    }

    /**
     * @inheritDoc
     */
    public function getMethodName(): string
    {
        return $this->sMethodName;
    }

    /**
     * @inheritDoc
     */
    public function func(): string
    {
        return $this->sMethodName;
    }
}
