<?php

/**
 * Config.php - Trait for config functions
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Features;

use function jaxon;

trait Config
{
    /**
     * Set the value of a config option
     *
     * @param string        $sName                The option name
     * @param mixed         $sValue               The option value
     *
     * @return void
     */
    public function setOption(string $sName, string $sValue)
    {
        jaxon()->di()->getConfig()->setOption($sName, $sValue);
    }

    /**
     * Get the value of a config option
     *
     * @param string        $sName              The option name
     * @param mixed|null    $xDefault           The default value, to be returned if the option is not defined
     *
     * @return mixed
     */
    public function getOption(string $sName, $xDefault = null)
    {
        return jaxon()->di()->getConfig()->getOption($sName, $xDefault);
    }

    /**
     * Check the presence of a config option
     *
     * @param string        $sName              The option name
     *
     * @return bool
     */
    public function hasOption(string $sName): bool
    {
        return jaxon()->di()->getConfig()->hasOption($sName);
    }

    /**
     * Get the names of the options matching a given prefix
     *
     * @param string        $sPrefix        The prefix to match
     *
     * @return array
     */
    public function getOptionNames(string $sPrefix): array
    {
        return jaxon()->di()->getConfig()->getOptionNames($sPrefix);
    }
}
