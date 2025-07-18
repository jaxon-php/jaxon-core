<?php

/**
 * ConfigTrait.php
 *
 * Config functions for classes with the config manager as attribute.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Config;

trait ConfigTrait
{
    /**
     * @return ConfigManager
     */
    abstract protected function config(): ConfigManager;

    /**
     * Get the value of a config option
     *
     * @param string $sName The option name
     * @param mixed $xDefault The default value, to be returned if the option is not defined
     *
     * @return mixed
     */
    public function getLibOption(string $sName, $xDefault = null): mixed
    {
        return $this->config()->getOption($sName, $xDefault);
    }

    /**
     * Check the presence of a config option
     *
     * @param string $sName The option name
     *
     * @return bool
     */
    public function hasLibOption(string $sName): bool
    {
        return $this->config()->hasOption($sName);
    }

    /**
     * Set the value of a config option
     *
     * @param string $sName The option name
     * @param mixed $xValue The option value
     *
     * @return void
     */
    public function setLibOption(string $sName, $xValue): void
    {
        $this->config()->setOption($sName, $xValue);
    }

    /**
     * Get the value of an application config option
     *
     * @param string $sName The option name
     * @param mixed $xDefault The default value, to be returned if the option is not defined
     *
     * @return mixed
     */
    public function getAppOption(string $sName, $xDefault = null): mixed
    {
        return $this->config()->getAppOption($sName, $xDefault);
    }

    /**
     * Check the presence of an application config option
     *
     * @param string $sName The option name
     *
     * @return bool
     */
    public function hasAppOption(string $sName): bool
    {
        return $this->config()->hasAppOption($sName);
    }

    /**
     * Set the value of a config option
     *
     * @param string $sName The option name
     * @param mixed $xValue The option value
     *
     * @return void
     */
    public function setAppOption(string $sName, $xValue): void
    {
        $this->config()->setAppOption($sName, $xValue);
    }
}
