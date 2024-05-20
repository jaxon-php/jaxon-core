<?php

/**
 * AbstractCall.php
 *
 * Base class for js call and selector classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\JsCall;

abstract class AbstractCall implements ParameterInterface
{
    /**
     * @var bool
     */
    protected $bToInt = false;

    /**
     * @return self
     */
    public function toInt(): self
    {
        $this->bToInt = true;
        return $this;
    }

    /**
     * return array
     */
    protected function toIntCall(): array
    {
        return [
            '_type' => 'method',
            '_name' => 'toInt',
            'args' => [],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'expr';
    }
}
