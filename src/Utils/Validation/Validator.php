<?php

/**
 * Validator.php - Jaxon input data validator
 *
 * Validate requests data before the are passed into the library.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Validation;

/*
 * See the following links to get explanations about the regexp.
 * http://php.net/manual/en/language.oop5.basic.php
 * http://stackoverflow.com/questions/3195614/validate-class-method-names-with-regex
 * http://www.w3schools.com/charsets/ref_html_utf8.asp
 * http://www.w3schools.com/charsets/ref_utf_latin1_supplement.asp
 */
class Validator
{
    /**
     * The translator
     *
     * @var \Jaxon\Utils\Translation\Translator
     */
    protected $xTranslator;

    /**
     * The config manager
     *
     * @var \Jaxon\Utils\Config\Config
     */
    protected $xConfig;

    /**
     * The last error message
     *
     * @var string
     */
    protected $sErrorMessage;

    public function __construct($xTranslator, $xConfig)
    {
        // Set the translator
        $this->xTranslator = $xTranslator;
        // Set the config manager
        $this->xConfig = $xConfig;
    }

    /**
     * Get the last error message
     *
     * @return string          The last error message
     */
    public function getErrorMessage()
    {
        return $this->sErrorMessage;
    }

    /**
     * Validate a function name
     *
     * @param string        $sName            The function name
     *
     * @return boolean            True if the function name is valid, and false if not
     */
    public function validateFunction($sName)
    {
        $this->sErrorMessage = '';
        return (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $sName) > 0);
    }

    /**
     * Validate an event name
     *
     * @param string        $sName            The event name
     *
     * @return boolean            True if the event name is valid, and false if not
     */
    public function validateEvent($sName)
    {
        $this->sErrorMessage = '';
        return (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $sName) > 0);
    }

    /**
     * Validate a class name
     *
     * @param string        $sName            The class name
     *
     * @return boolean            True if the class name is valid, and false if not
     */
    public function validateClass($sName)
    {
        $this->sErrorMessage = '';
        return (preg_match('/^([a-zA-Z][a-zA-Z0-9_]*)(\.[a-zA-Z][a-zA-Z0-9_]*)*$/', $sName) > 0);
    }

    /**
     * Validate a method name
     *
     * @param string        $sName            The function name
     *
     * @return boolean            True if the method name is valid, and false if not
     */
    public function validateMethod($sName)
    {
        $this->sErrorMessage = '';
        // return (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $sName) > 0);
        return (preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $sName) > 0);
    }

    /**
     * Validate a property of an uploaded file
     *
     * @param string        $sName          The uploaded file variable name
     * @param string        $sValue         The value of the property
     * @param string        $sProperty      The property name in config options
     * @param string        $sField         The field name in file data
     *
     * @return boolean            True if the property valid, and false if not
     */
    private function validateFileProperty($sName, $sValue, $sProperty, $sField)
    {
        $xDefault = $this->xConfig->getOption('upload.default.' . $sProperty);
        $aAllowed = $this->xConfig->getOption('upload.files.' . $sName . '.' . $sProperty, $xDefault);
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
     * @param string        $sName          The uploaded file variable name
     * @param integer       $iFileSize      The uploaded file size
     * @param string        $sProperty      The property name in config options
     *
     * @return boolean            True if the property valid, and false if not
     */
    private function validateFileSize($sName, $iFileSize, $sProperty)
    {
        $xDefault = $this->xConfig->getOption('upload.default.' . $sProperty, 0);
        $iSize = $this->xConfig->getOption('upload.files.' . $sName . '.' . $sProperty, $xDefault);
        if($iSize > 0 && (
            ($sProperty == 'max-size' && $iFileSize > $iSize) ||
            ($sProperty == 'min-size' && $iFileSize < $iSize)))
        {
            $this->sErrorMessage = $this->xTranslator->trans('errors.upload.' . $sProperty, ['size' => $iFileSize]);
            return false;
        }
        return true;
    }

    /**
     * Validate an uploaded file
     *
     * @param string        $sName            The uploaded file variable name
     * @param array         $aUploadedFile    The file data received in the $_FILES array
     *
     * @return boolean            True if the file data are valid, and false if not
     */
    public function validateUploadedFile($sName, array $aUploadedFile)
    {
        $this->sErrorMessage = '';
        // Verify the file extension
        if(!$this->validateFileProperty($sName, $aUploadedFile['type'], 'types', 'type'))
        {
            return false;
        }

        // Verify the file extension
        if(!$this->validateFileProperty($sName, $aUploadedFile['extension'], 'extensions', 'extension'))
        {
            return false;
        }

        // Verify the max size
        if(!$this->validateFileSize($sName, $aUploadedFile['size'], 'max-size'))
        {
            return false;
        }

        // Verify the min size
        if(!$this->validateFileSize($sName, $aUploadedFile['size'], 'min-size'))
        {
            return false;
        }

        return true;
    }
}
