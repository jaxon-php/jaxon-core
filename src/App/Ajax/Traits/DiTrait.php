<?php

/**
 * DiTrait.php
 *
 * DI containers.
 *
 * @package jaxon-core
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax\Traits;

use Jaxon\App\Config\ConfigManager;
use Jaxon\Di\ComponentContainer;
use Jaxon\Di\Container;

trait DiTrait
{
    /**
     * @var Container
     */
    private Container $xContainer;

    /**
     * @var ComponentContainer
     */
    protected ComponentContainer $xComponentContainer;

    /**
     * Get the DI container
     *
     * @return Container
     */
    public function di(): Container
    {
        return $this->xContainer;
    }

    /**
     * Get the component DI container
     *
     * @return ComponentContainer
     */
    public function cdi(): ComponentContainer
    {
        return $this->xComponentContainer;
    }

    /**
     * @return ConfigManager
     */
    public function config(): ConfigManager
    {
        return $this->di()->g(ConfigManager::class);
    }
}
