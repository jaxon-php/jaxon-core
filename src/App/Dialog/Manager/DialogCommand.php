<?php

/**
 * DialogCommand.php
 *
 * Facade for dialogs functions.
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog\Manager;

use Jaxon\Script\Call\Parameter;

use function array_map;

class DialogCommand
{
    /**
     * The next message library
     *
     * @var string
     */
    private $sLibrary = '';

    /**
     * The next message title
     *
     * @var string
     */
    private $sTitle = '';

    /**
     * The constructor
     *
     * @param LibraryRegistryInterface|null $xRegistry
     */
    public function __construct(private ?LibraryRegistryInterface $xRegistry)
    {}

    /**
     * Set the library for the next message.
     *
     * @param string $sLibrary     The name of the library
     *
     * @return void
     */
    public function library(string $sLibrary)
    {
        $this->sLibrary = $sLibrary;
    }

    /**
     * Set the title of the next message.
     *
     * @param string $sTitle     The title of the message
     *
     * @return void
     */
    public function title(string $sTitle)
    {
        $this->sTitle = $sTitle;
    }

    /**
     * @param string $sStr
     * @param array $aArgs
     *
     * @return array
     */
    private function phrase(string $sStr, array $aArgs = []): array
    {
        return [
            'str' => $sStr,
            'args' => array_map(function($xArg) {
                return Parameter::make($xArg);
            }, $aArgs),
        ];
    }

    /**
     * @return string
     */
    private function getLibrary(): string
    {
        $sLibrary = $this->sLibrary;
        $this->sLibrary = '';
        return $sLibrary;
    }

    /**
     * @return string
     */
    private function getTitle(): string
    {
        $sTitle = $this->sTitle;
        $this->sTitle = '';
        return $sTitle;
    }

    /**
     * Print an alert message.
     *
     * @param string $sType     The type of the message
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return array
     */
    private function alert(string $sType, string $sMessage, array $aArgs): array
    {
        return [
            'lib' => $this->getLibrary() ?:
                ($this->xRegistry?->getAlertLibrary()->getName() ?? ''),
            'type' => $sType,
            'title' => $this->getTitle(),
            'phrase' => $this->phrase($sMessage, $aArgs),
        ];
    }

    /**
     * Show a success message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return array
     */
    public function success(string $sMessage, array $aArgs = []): array
    {
        return $this->alert('success', $sMessage, $aArgs);
    }

    /**
     * Show an information message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return array
     */
    public function info(string $sMessage, array $aArgs = []): array
    {
        return $this->alert('info', $sMessage, $aArgs);
    }

    /**
     * Show a warning message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return array
     */
    public function warning(string $sMessage, array $aArgs = []): array
    {
        return $this->alert('warning', $sMessage, $aArgs);
    }

    /**
     * Show an error message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return array
     */
    public function error(string $sMessage, array $aArgs = []): array
    {
        return $this->alert('error', $sMessage, $aArgs);
    }

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
            'lib' => $this->getLibrary() ?:
                ($this->xRegistry?->getModalLibrary()->getName() ?? ''),
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
            'lib' => $this->getLibrary() ?:
                ($this->xRegistry?->getModalLibrary()->getName() ?? ''),
        ];
    }

    /**
     * Add a confirm question to a function call.
     *
     * @param string $sQuestion
     * @param array $aArgs
     *
     * @return array
     */
    public function confirm(string $sQuestion, array $aArgs = []): array
    {
        return [
            'lib' => $this->getLibrary() ?:
                ($this->xRegistry?->getConfirmLibrary()->getName() ?? ''),
            'title' => $this->getTitle(),
            'phrase' => $this->phrase($sQuestion, $aArgs),
        ];
    }
}
