<?php

/**
 * Alert.php - Interface for alert messages.
 *
 * @package jaxon-dialogs
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Interfaces;

interface Alert
{
    /**
     * Tells if the library should return the javascript code or run it in the browser.
     *
     * @param boolean             $return               Whether to return the code
     *
     * @return void
     */
    public function setReturn($return);

    /**
     * Tells if the library should return the js code or run it in the browser.
     *
     * @return void
     */
    public function getReturn();

    /**
     * Print a success message.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return void
     */
    public function success($message, $title = null);

    /**
     * Print an information message.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return void
     */
    public function info($message, $title = null);

    /**
     * Print a warning message.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return void
     */
    public function warning($message, $title = null);

    /**
     * Print an error message.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return void
     */
    public function error($message, $title = null);
}
