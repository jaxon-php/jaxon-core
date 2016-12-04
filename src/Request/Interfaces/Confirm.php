<?php

/**
 * Confirm.php - A confirm question for a Jaxon request
 *
 * Interface for adding a confirmation question which is asked before calling a Jaxon function.
 *
 * @package jaxon-core
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Interfaces;

interface Confirm
{
    /**
     * Get the script which makes a call only if the user answers yes to the given question
     * 
     * @return string
     */
    public function getScriptWithQuestion($question, $script);
}
