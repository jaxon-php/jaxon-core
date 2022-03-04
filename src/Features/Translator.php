<?php

/**
 * Translator.php - Trait for translation functions
 *
 * Make functions of the utils classes available to Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Features;

use function jaxon;

trait Translator
{
    /**
     * Get a translated string
     *
     * @param string        $sText                The key of the translated string
     * @param array         $aPlaceHolders        The placeholders of the translated string
     * @param string        $sLanguage            The language of the translated string
     *
     * @return string
     */
    public function trans(string $sText, array $aPlaceHolders = [], string $sLanguage = ''): string
    {
        return jaxon()->di()->getTranslator()->trans($sText, $aPlaceHolders, $sLanguage);
    }
}
