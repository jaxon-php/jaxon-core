<?php

namespace Jaxon\App\I18n;

/**
 * Translator.php
 *
 * A translator coupled with a config event listener.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Config\ConfigListenerInterface;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Translation\Translator as BaseTranslator;

class Translator extends BaseTranslator implements ConfigListenerInterface
{
    /**
     * @inheritDoc
     */
    public function onChange(Config $xConfig, string $sName)
    {
        // Set the library language any time the config is changed.
        if($sName === '' || $sName === 'core.language')
        {
            $this->setLocale($xConfig->getOption('core.language'));
        }
    }
}
