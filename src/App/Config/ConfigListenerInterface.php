<?php

/**
 * ConfigListenerInterface.php
 *
 * Listener interface for config changes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Config;

use Jaxon\Utils\Config\Config;

interface ConfigListenerInterface
{
    /**
     * Multiple config options changed
     *
     * @param Config $xConfig
     *
     * @return void
     */
    public function onChanges(Config $xConfig);

    /**
     * Single config option changed
     *
     * @param Config $xConfig
     * @param string $sName The option name
     *
     * @return void
     */
    public function onChange(Config $xConfig, string $sName);
}
