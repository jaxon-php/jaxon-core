<?php

/**
 * Validator.php - Jaxon input data validator
 *
 * Validate requests data before the are passed into the library.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils;

/*
 * See the following links to get explanations about the regexp.
 * http://php.net/manual/en/language.oop5.basic.php
 * http://stackoverflow.com/questions/3195614/validate-class-method-names-with-regex
 * http://www.w3schools.com/charsets/ref_html_utf8.asp
 * http://www.w3schools.com/charsets/ref_utf_latin1_supplement.asp
 */
class Validator
{
    protected $xValidator;

    public function __construct()
    {
        $this->xValidator = false;
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
        // return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $sName);
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $sName);
    }
}
