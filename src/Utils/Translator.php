<?php

/**
 * Translator.php - Translator
 *
 * Provide translation service for strings in the Jaxon library.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils;

class Translator
{
    protected $xConfig;
    protected $sDefaultLocale = 'en';
    protected $sResourceDir;
    // Translations
    protected $aMessages;

    public function __construct($sResourceDir, $xConfig)
    {
        // Translations
        $this->aMessages = array();
        // Set the translation resource directory
        $this->sResourceDir = trim($sResourceDir);
        // Set the config manager
        $this->xConfig = $xConfig;
        // Load the Jaxon package translations
        $this->loadMessages($this->sResourceDir . '/en/errors.php', 'en');
        $this->loadMessages($this->sResourceDir . '/fr/errors.php', 'fr');
    }

    /**
     * Load translated strings from a file
     *
     * @param string        $sFilePath            The file full path
     * @param string        $sLanguage            The language of the strings in this file
     *
     * @return void
     */
    public function loadMessages($sFilePath, $sLanguage)
    {
        if(!file_exists($sFilePath))
        {
            return;
        }
        $aMessages = require($sFilePath);
        if(!is_array($aMessages))
        {
            return;
        }
        // Load the translations
        if(!array_key_exists($sLanguage, $this->aMessages))
        {
            $this->aMessages[$sLanguage] = $aMessages;
        }
        else
        {
            $this->aMessages[$sLanguage] = array_merge($aMessages, $this->aMessages[$sLanguage]);
        }
    }

    /**
     * Get a translated string
     *
     * @param string        $sText                The key of the translated string
     * @param string        $aPlaceHolders        The placeholders of the translated string
     * @param string        $sLanguage            The language of the translated string
     *
     * @return string        The translated string
     */
    public function trans($sText, array $placeholders = array(), $sLanguage = null)
    {
        $sText = trim((string)$sText);
        if(!$sLanguage)
        {
            $sLanguage = $this->xConfig->getOption('language');
        }
        if(!$sLanguage)
        {
            $sLanguage = $this->sDefaultLocale;
        }
        if(!array_key_exists($sLanguage, $this->aMessages) || !array_key_exists($sText, $this->aMessages[$sLanguage]))
        {
           return $sText;
        }
        $message = $this->aMessages[$sLanguage][$sText];
        foreach($placeholders as $name => $value)
        {
            $message = str_replace(":$name", $value, $message);
        }
        return $message;
    }
}
