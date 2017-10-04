<?php

/**
 * Translator.php - Translator
 *
 * Provide translation service for strings in the Jaxon library.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Translation;

class Translator
{
    protected $xConfig;
    protected $sDefaultLocale = 'en';
    protected $sResourceDir;
    // Translations
    protected $aTranslations;

    public function __construct($sResourceDir, $xConfig)
    {
        // Translations
        $this->aTranslations = array();
        // Set the translation resource directory
        $this->sResourceDir = trim($sResourceDir);
        // Set the config manager
        $this->xConfig = $xConfig;
        // Load the Jaxon package translations
        $this->loadTranslations($this->sResourceDir . '/en/errors.php', 'en');
        $this->loadTranslations($this->sResourceDir . '/fr/errors.php', 'fr');
        $this->loadTranslations($this->sResourceDir . '/es/errors.php', 'es');
        // Load the config translations
        $this->loadTranslations($this->sResourceDir . '/en/config.php', 'en');
        $this->loadTranslations($this->sResourceDir . '/fr/config.php', 'fr');
        $this->loadTranslations($this->sResourceDir . '/es/config.php', 'es');
        // Load the upload translations
        $this->loadTranslations($this->sResourceDir . '/en/upload.php', 'en');
        $this->loadTranslations($this->sResourceDir . '/fr/upload.php', 'fr');
        $this->loadTranslations($this->sResourceDir . '/es/upload.php', 'es');
    }

    /**
     * Recursively load translated strings from a array
     *
     * @param string            $sLanguage            The language of the translations
     * @param string            $sPrefix              The prefix for names
     * @param array             $aTranslations        The translated strings
     *
     * @return void
     */
    private function _loadTranslations($sLanguage, $sPrefix, array $aTranslations)
    {
        foreach($aTranslations as $sName => $xTranslation)
        {
            $sName = trim($sName);
            $sName = ($sPrefix) ? $sPrefix . '.' . $sName : $sName;
            if(!is_array($xTranslation))
            {
                // Save this translation
                $this->aTranslations[$sLanguage][$sName] = $xTranslation;
            }
            else
            {
                // Recursively read the translations in the array
                $this->_loadTranslations($sLanguage, $sName, $xTranslation);
            }
        }
    }

    /**
     * Load translated strings from a file
     *
     * @param string        $sFilePath            The file full path
     * @param string        $sLanguage            The language of the strings in this file
     *
     * @return void
     */
    public function loadTranslations($sFilePath, $sLanguage)
    {
        if(!file_exists($sFilePath))
        {
            return;
        }
        $aTranslations = require($sFilePath);
        if(!is_array($aTranslations))
        {
            return;
        }
        // Load the translations
        if(!array_key_exists($sLanguage, $this->aTranslations))
        {
            $this->aTranslations[$sLanguage] = [];
        }
        $this->_loadTranslations($sLanguage, '', $aTranslations);
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
    public function trans($sText, array $aPlaceHolders = array(), $sLanguage = null)
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
        if(!array_key_exists($sLanguage, $this->aTranslations) || !array_key_exists($sText, $this->aTranslations[$sLanguage]))
        {
           return $sText;
        }
        $message = $this->aTranslations[$sLanguage][$sText];
        foreach($aPlaceHolders as $name => $value)
        {
            $message = str_replace(':' . $name, $value, $message);
        }
        return $message;
    }
}
