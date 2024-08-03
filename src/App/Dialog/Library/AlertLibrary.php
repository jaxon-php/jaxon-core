<?php

/**
 * AlertLibrary.php
 *
 * Implements the dialog message and question features using the js alert and confirm functions.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog\Library;

use Jaxon\App\Dialog\LibraryInterface;
use Jaxon\App\Dialog\MessageInterface;
use Jaxon\App\Dialog\QuestionInterface;

class AlertLibrary implements LibraryInterface, MessageInterface, QuestionInterface
{
    use DialogLibraryTrait;

    /**
     * Get the library name
     *
     * @return string
     */
    public function getName(): string
    {
        return ''; // No name
    }
}
