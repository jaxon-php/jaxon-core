<?php

/**
 * LibConfigTrait.php
 *
 * Read and set application config options.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax;

use Jaxon\App\Config\ConfigManager;

trait AppConfigTrait
{
    /**
     * @return ConfigManager
     */
    abstract protected function getConfigManager(): ConfigManager;

    /**
     * Set the value of a config option
     *
     * @param string $sName    The option name
     * @param mixed $sValue    The option value
     *
     * @return void
     */
    public function setOption(string $sName, $sValue)
    {
        $this->getConfigManager()->setAppOption($sName, $sValue);
    }

    /**
     * Get the value of a config option
     *
     * @param string $sName    The option name
     * @param mixed $xDefault    The default value, to be returned if the option is not defined
     *
     * @return mixed
     */
    public function getOption(string $sName, $xDefault = null)
    {
        return $this->getConfigManager()->getAppOption($sName, $xDefault);
    }

    /**
     * Check the presence of a config option
     *
     * @param string $sName    The option name
     *
     * @return bool
     */
    public function hasOption(string $sName): bool
    {
        return $this->getConfigManager()->hasAppOption($sName);
    }
}
