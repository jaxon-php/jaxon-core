<?php

/**
 * ToInt.php
 *
 * Trait with functions to convert selector and call value to int.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Js\Traits;

trait ToInt
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
}
