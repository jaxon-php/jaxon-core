<?php

/**
 * Validator.php - Jaxon request data validator
 *
 * Validate requests data before the are passed into the library.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request;

/*
 * See the following links to get explanations about the regexp.
 * http://php.net/manual/en/language.oop5.basic.php
 * http://stackoverflow.com/questions/3195614/validate-class-method-names-with-regex
 * http://www.w3schools.com/charsets/ref_html_utf8.asp
 * http://www.w3schools.com/charsets/ref_utf_latin1_supplement.asp
 */

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Request\Upload\File;

use function in_array;
use function is_array;
use function preg_match;

class Validator
{
    /**
     * The config manager
     *
     * @var ConfigManager
     */
    protected $xConfigManager;

    /**
     * The translator
     *
     * @var Translator
     */
    protected $xTranslator;

    /**
     * The last error message
     *
     * @var string
     */
    protected $sErrorMessage;

    public function __construct(ConfigManager $xConfigManager, Translator $xTranslator)
    {
        // Set the config manager
        $this->xConfigManager = $xConfigManager;
        // Set the translator
        $this->xTranslator = $xTranslator;
    }

    /**
     * Get the last error message
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->sErrorMessage;
    }

    /**
     * Validate a function name
     *
     * @param string $sName    The function name
     *
     * @return bool
     */
    public function validateFunction(string $sName): bool
    {
        $this->sErrorMessage = '';
        return (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $sName) > 0);
    }

    /**
     * Validate a class name
     *
     * @param string $sName    The class name
     *
     * @return bool
     */
    public function validateClass(string $sName): bool
    {
        $this->sErrorMessage = '';
        return (preg_match('/^([a-zA-Z][a-zA-Z0-9_]*)(\.[a-zA-Z][a-zA-Z0-9_]*)*$/', $sName) > 0);
    }

    /**
     * Validate a method name
     *
     * @param string $sName    The function name
     *
     * @return bool
     */
    public function validateMethod(string $sName): bool
    {
        $this->sErrorMessage = '';
        // return (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $sName) > 0);
        return (preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $sName) > 0);
    }

    /**
     * Validate a temp file name
     *
     * @param string $sName    The temp file name
     *
     * @return bool
     */
    public function validateTempFileName(string $sName): bool
    {
        $this->sErrorMessage = '';
        return (preg_match('/^[a-zA-Z0-9_\x7f-\xff]*$/', $sName) > 0);
    }

    /**
     * Validate a property of an uploaded file
     *
     * @param string $sName    The uploaded file variable name
     * @param string $sValue    The value of the property
     * @param string $sProperty    The property name in config options
     * @param string $sField    The field name in file data
     *
     * @return bool
     */
    private function validateFileProperty(string $sName, string $sValue, string $sProperty, string $sField): bool
    {
        $xDefault = $this->xConfigManager->getOption('upload.default.' . $sProperty);
        $aAllowed = $this->xConfigManager->getOption('upload.files.' . $sName . '.' . $sProperty, $xDefault);
        if(is_array($aAllowed) && !in_array($sValue, $aAllowed))
        {
            $this->sErrorMessage = $this->xTranslator->trans('errors.upload.' . $sField, [$sField => $sValue]);
            return false;
        }
        return true;
    }

    /**
     * Validate the size of an uploaded file
     *
     * @param string $sName    The uploaded file variable name
     * @param int $nFileSize    The uploaded file size
     * @param string $sProperty    The property name in config options
     *
     * @return bool
     */
    private function validateFileSize(string $sName, int $nFileSize, string $sProperty): bool
    {
        $xDefault = $this->xConfigManager->getOption('upload.default.' . $sProperty, 0);
        $nSize = $this->xConfigManager->getOption('upload.files.' . $sName . '.' . $sProperty, $xDefault);
        if($nSize > 0 && (
            ($sProperty == 'max-size' && $nFileSize > $nSize) ||
            ($sProperty == 'min-size' && $nFileSize < $nSize)))
        {
            $this->sErrorMessage = $this->xTranslator->trans('errors.upload.' . $sProperty, ['size' => $nFileSize]);
            return false;
        }
        return true;
    }

    /**
     * Validate an uploaded file
     *
     * @param string $sName    The uploaded file variable name
     * @param File $xFile    The uploaded file
     *
     * @return bool
     */
    public function validateUploadedFile(string $sName, File $xFile): bool
    {
        $this->sErrorMessage = '';
        // Verify the file extension
        if(!$this->validateFileProperty($sName, $xFile->type(), 'types', 'type'))
        {
            return false;
        }

        // Verify the file extension
        if(!$this->validateFileProperty($sName, $xFile->extension(), 'extensions', 'extension'))
        {
            return false;
        }

        // Verify the max size
        if(!$this->validateFileSize($sName, $xFile->size(), 'max-size'))
        {
            return false;
        }

        // Verify the min size
        if(!$this->validateFileSize($sName, $xFile->size(), 'min-size'))
        {
            return false;
        }

        return true;
    }
}
