<?php

/**
 * Validator.php
 *
 * Validate requests data before they are passed into the library.
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

    public function __construct(ConfigManager $xConfigManager, Translator $xTranslator)
    {
        // Set the config manager
        $this->xConfigManager = $xConfigManager;
        // Set the translator
        $this->xTranslator = $xTranslator;
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
        return (preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $sName) > 0);
    }
}
