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
use Jaxon\Utils\Config\Config;

class ConfigEventManager implements ConfigListenerInterface
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * @var string[]
     */
    protected $aListeners = [];

    /**
     * The constructor
     *
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * Add a listener
     *
     * @param string $sClassName
     *
     * @return void
     */
    public function addListener(string $sClassName)
    {
        $this->aListeners[] = $sClassName;
    }

    /**
     * @inheritDoc
     */
    public function onChange(Config $xConfig, string $sName)
    {
        foreach($this->aListeners as $sListener)
        {
            $this->di->g($sListener)->onChange($xConfig, $sName);
        }
    }
}
