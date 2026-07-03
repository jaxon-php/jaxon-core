<?php

/**
 * CallableFunction.php
 *
 * This class contains the name of the function targetted by a Jaxon request.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Request\CallableFunction;

use Jaxon\Request\CallableAction;

class CallableFunction extends CallableAction
{
    /**
     * @param string $sFunctionName
     * @param array $aActionArgs
     */
    public function __construct(private string $sFunctionName, array $aActionArgs)
    {
        $this->aActionArgs = $aActionArgs;
    }

    /**
     * @inheritDoc
     */
    public function isFunction(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isClass(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getFunctionName(): string
    {
        return $this->sFunctionName;
    }

    /**
     * @inheritDoc
     */
    public function getClassName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getMethodName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function func(): string
    {
        return $this->sFunctionName;
    }
}
