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
    use MessageTrait;

    /**
     * Get the library name
     *
     * @return string
     */
    public function getName(): string
    {
        return ''; // No name
    }

    /**
     * Get the script which makes a call only if the user answers yes to the given question
     *
     * @param string  $sQuestion
     * @param string  $sYesScript
     * @param string  $sNoScript
     *
     * @return string
     */
    public function confirm(string $sQuestion, string $sYesScript, string $sNoScript): string
    {
        return empty($sNoScript) ? 'if(confirm(' . $sQuestion . ')){' . $sYesScript . ';}' :
            'if(confirm(' . $sQuestion . ')){' . $sYesScript . ';}else{' . $sNoScript . ';}';
    }

    /**
     * @inheritDoc
     */
    protected function alert(string $sMessage, string $sTitle, string $sType)
    {
        if(!empty($sTitle))
        {
            $sMessage = '<b>' . $sTitle . '</b><br/>' . $sMessage;
        }
        $this->xResponse->alert($sMessage, $sTitle);
    }
}
