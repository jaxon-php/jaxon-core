<?php

/**
 * Message.php - Interface for alert messages.
 *
 * @package jaxon-dialogs
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Contracts\Dialogs;

interface Message
{
    /**
     * Tells if the library should return the javascript code or run it in the browser.
     *
     * @param bool             $bReturn              Whether to return the code
     *
     * @return void
     */
    public function setReturn(bool $bReturn);

    /**
     * Tells if the library should return the js code or run it in the browser.
     *
     * @return void
     */
    public function getReturn();

    /**
     * Print a success message.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return void
     */
    public function success(string $sMessage, string $sTitle = '');

    /**
     * Print an information message.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return void
     */
    public function info(string $sMessage, string $sTitle = '');

    /**
     * Print a warning message.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return void
     */
    public function warning(string $sMessage, string $sTitle = '');

    /**
     * Print an error message.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return void
     */
    public function error(string $sMessage, string $sTitle = '');
}
