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
     * @return bool            True if the function name is valid, and false if not
     */
    public function validateFunction($sName)
    {
        $this->sErrorMessage = '';
        return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $sName);
    }

    /**
     * Validate an event name
     *
     * @param string        $sName            The event name
     *
     * @return bool            True if the event name is valid, and false if not
     */
    public function validateEvent($sName)
    {
        $this->sErrorMessage = '';
        return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $sName);
    }

    /**
     * Validate a class name
     *
     * @param string        $sName            The class name
     *
     * @return bool            True if the class name is valid, and false if not
     */
    public function validateClass($sName)
    {
        $this->sErrorMessage = '';
        return preg_match('/^([a-zA-Z][a-zA-Z0-9_]*)(\.[a-zA-Z][a-zA-Z0-9_]*)*$/', $sName);
    }

    /**
     * Validate a method name
     *
     * @param string        $sName            The function name
     *
     * @return bool            True if the method name is valid, and false if not
     */
    public function validateMethod($sName)
    {
        $this->sErrorMessage = '';
        // return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $sName);
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $sName);
    }

    /**
     * Validate an uploaded file
     *
     * @param string        $sName            The uploaded file variable name
     * @param array         $aUploadedFile    The file data received in the $_FILES array
     *
     * @return bool            True if the file data are valid, and false if not
     */
    public function validateUploadedFile($sName, array $aUploadedFile)
    {
        $this->sErrorMessage = '';
        // Verify the file extension
        $xDefault = $this->xConfig->getOption('upload.default.types');
        $aAllowed = $this->xConfig->getOption('upload.files.' . $sName . '.types', $xDefault);
        if(is_array($aAllowed) && !in_array($aUploadedFile['type'], $aAllowed))
        {
            $this->sErrorMessage = $this->xTranslator->trans('errors.upload.type', $aUploadedFile);
            return false;
        }
        // Verify the file extension
        $xDefault = $this->xConfig->getOption('upload.default.extensions');
        $aAllowed = $this->xConfig->getOption('upload.files.' . $sName . '.extensions', $xDefault);
        if(is_array($aAllowed) && !in_array($aUploadedFile['extension'], $aAllowed))
        {
            $this->sErrorMessage = $this->xTranslator->trans('errors.upload.extension', $aUploadedFile);
            return false;
        }
        // Verify the max size
        $xDefault = $this->xConfig->getOption('upload.default.max-size', 0);
        $iSize = $this->xConfig->getOption('upload.files.' . $sName . '.max-size', $xDefault);
        if($iSize > 0 && $aUploadedFile['size'] > $iSize)
        {
            $this->sErrorMessage = $this->xTranslator->trans('errors.upload.max-size', $aUploadedFile);
            return false;
        }
        // Verify the min size
        $xDefault = $this->xConfig->getOption('upload.default.min-size', 0);
        $iSize = $this->xConfig->getOption('upload.files.' . $sName . '.min-size', $xDefault);
        if($iSize > 0 && $aUploadedFile['size'] < $iSize)
        {
            $this->sErrorMessage = $this->xTranslator->trans('errors.upload.min-size', $aUploadedFile);
            return false;
        }
        return true;
    }
}
