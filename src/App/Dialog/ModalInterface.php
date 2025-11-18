<?php

/**
 * DialogInterface.php
 *
 * Defines the modal functions of the dialog plugin.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog;

interface ModalInterface
{
    /**
     * Show a modal dialog.
     *
     * @param string $sTitle The title of the dialog
     * @param string $sContent The content of the dialog
     * @param array $aButtons The buttons of the dialog
     * @param array $aOptions The options of the dialog
     *
     * @return void
     */
    public function show(string $sTitle, string $sContent, array $aButtons = [], array $aOptions = []): void;

    /**
     * Hide the modal dialog.
     *
     * @return void
     */
    public function hide(): void;
}
