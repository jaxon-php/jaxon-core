<?php

/**
 * ModalTrait.php - Show and hide dialogs.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog\Library;

use Jaxon\App\Dialog\ModalInterface;

trait ModalTrait
{
    /**
     * Get the ModalInterface library
     *
     * @return ModalInterface|null
     */
    abstract public function getModalLibrary(): ?ModalInterface;

    /**
     * Show a modal dialog.
     *
     * @param string $sTitle The title of the dialog
     * @param string $sContent The content of the dialog
     * @param array $aButtons The buttons of the dialog
     * @param array $aOptions The options of the dialog
     *
     * Each button is an array with the following entries:
     * - title: the text to be printed in the button
     * - class: the CSS class of the button
     * - click: the javascript function to be called when the button is clicked
     * If the click value is set to "close", then the button closes the dialog.
     *
     * The content of the $aOptions depends on the javascript library in use.
     * Check their specific documentation for more information.
     *
     * @return array
     */
    public function show(string $sTitle, string $sContent, array $aButtons, array $aOptions = []): array
    {
        return [
            'lib' => $this->getModalLibrary()->getName(),
            'dialog' => [
                'title' => $sTitle,
                'content' => $sContent,
                'buttons' => $aButtons,
                'options' => $aOptions,
            ],
        ];
    }

    /**
     * Hide the modal dialog.
     *
     * @return array
     */
    public function hide(): array
    {
        return [
            'lib' => $this->getModalLibrary()->getName(),
        ];
    }
}
