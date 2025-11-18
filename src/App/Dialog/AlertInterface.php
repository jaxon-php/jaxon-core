<?php

/**
 * AlertInterface.php
 *
 * Defines the message functions of the dialog plugin.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog;

interface AlertInterface
{
    /**
     * Set the title of the next message.
     *
     * @param string $sTitle     The title of the message
     *
     * @return AlertInterface
     */
    public function title(string $sTitle): AlertInterface;

    /**
     * Show a success message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return void
     */
    public function success(string $sMessage, ...$aArgs): void;

    /**
     * Show an information message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return void
     */
    public function info(string $sMessage, ...$aArgs): void;

    /**
     * Show a warning message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return void
     */
    public function warning(string $sMessage, ...$aArgs): void;

    /**
     * Show an error message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return void
     */
    public function error(string $sMessage, ...$aArgs): void;
}
