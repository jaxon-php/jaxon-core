<?php

/**
 * DialogPluginTrait.php
 *
 * Provides stub methods for the DialogPlugin class, so it can conform to the interfaces it implements.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-dialogs
 */

namespace Jaxon\Plugin\Response\Dialog;

trait DialogPluginTrait
{
    public function setReturnCode(bool $bReturnCode)
    {}

    public function getUri(): string
    {
        return '';
    }

    public function getSubdir(): string
    {
        return '';
    }

    public function getVersion(): string
    {
        return '';
    }
}
