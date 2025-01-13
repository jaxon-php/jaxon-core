<?php

/**
 * LibConfigTrait.php
 *
 * Read and set library config options.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax;

use Jaxon\App\Config\ConfigManager;

trait LibConfigTrait
{
    /**
     * @return ConfigManager
     */
    abstract protected function getConfigManager(): ConfigManager;

    /**
     * Set the values of an array of config options
     *
     * @param array $aOptions The options values to be set
     * @param string $sNamePrefix A prefix for the config option names
     *
     * @return bool
     */
    public function setOptions(array $aOptions, string $sNamePrefix = ''): bool
    {
        return $this->getConfigManager()->setOptions($aOptions, $sNamePrefix);
    }

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
        $this->getConfigManager()->setOption($sName, $sValue);
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
        return $this->getConfigManager()->getOption($sName, $xDefault);
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
        return $this->getConfigManager()->hasOption($sName);
    }
}
