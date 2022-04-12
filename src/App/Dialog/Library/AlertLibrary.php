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
     * Print an alert message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return string
     */
    private function alert(string $sMessage, string $sTitle): string
    {
        if(!empty($sTitle))
        {
            $sMessage = '<b>' . $sTitle . '</b><br/>' . $sMessage;
        }
        if($this->returnCode())
        {
            return 'alert(' . $sMessage . ')';
        }
        $this->xResponse->alert($sMessage, $sTitle);
        return '';
    }

    /**
     * Print a success message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return string
     */
    public function success(string $sMessage, string $sTitle = ''): string
    {
        return $this->alert($sMessage, $sTitle);
    }

    /**
     * Print an information message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return string
     */
    public function info(string $sMessage, string $sTitle = ''): string
    {
        return $this->alert($sMessage, $sTitle);
    }

    /**
     * Print a warning message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return string
     */
    public function warning(string $sMessage, string $sTitle = ''): string
    {
        return $this->alert($sMessage, $sTitle);
    }

    /**
     * Print an error message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return string
     */
    public function error(string $sMessage, string $sTitle = ''): string
    {
        return $this->alert($sMessage, $sTitle);
    }
}
