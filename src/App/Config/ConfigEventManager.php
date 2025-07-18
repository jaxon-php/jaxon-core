<?php

namespace Jaxon\App\Config;

/**
 * ConfigEventManager.php
 *
 * Call listeners on config changes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\Di\Container;
use Jaxon\Config\Config;

class ConfigEventManager
{
    /**
     * @var string[]
     */
    protected $aLibConfigListeners = [];

    /**
     * @var string[]
     */
    protected $aAppConfigListeners = [];

    /**
     * @param Container $di
     */
    public function __construct(private Container $di)
    {}

    /**
     * @param string $sClassName
     *
     * @return void
     */
    public function addLibConfigListener(string $sClassName): void
    {
        $this->aLibConfigListeners[] = $sClassName;
    }

    /**
     * @param string $sClassName
     *
     * @return void
     */
    public function addAppConfigListener(string $sClassName): void
    {
        $this->aAppConfigListeners[] = $sClassName;
    }

    /**
     * @inheritDoc
     */
    public function libConfigChanged(Config $xConfig, string $sName): void
    {
        foreach($this->aLibConfigListeners as $sListener)
        {
            $this->di->g($sListener)->onChange($xConfig, $sName);
        }
    }

    /**
     * @inheritDoc
     */
    public function appConfigChanged(Config $xConfig, string $sName): void
    {
        foreach($this->aAppConfigListeners as $sListener)
        {
            $this->di->g($sListener)->onChange($xConfig, $sName);
        }
    }
}
