<?php

/**
 * MessageInterface.php - Interface for alert messages.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog;

interface MessageInterface extends LibraryInterface
{
    /**
     * Show a success message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return string
     */
    public function success(string $sMessage, string $sTitle = ''): string;

    /**
     * Show an information message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return string
     */
    public function info(string $sMessage, string $sTitle = ''): string;

    /**
     * Show a warning message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return string
     */
    public function warning(string $sMessage, string $sTitle = ''): string;

    /**
     * Show an error message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return string
     */
    public function error(string $sMessage, string $sTitle = ''): string;
}
