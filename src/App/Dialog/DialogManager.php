<?php

/**
 * DialogManager.php
 *
 * Facade for dialogs functions.
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog;

use Jaxon\App\Dialog\Library\DialogLibraryManager;
use Jaxon\Request\Js\Parameter;

use function array_map;

class DialogManager
{
    /**
     * @var DialogLibraryManager
     */
    protected $xDialogLibraryManager;

    /**
     * The next message title
     *
     * @var string
     */
    private $sTitle = '';

    /**
     * The constructor
     *
     * @param DialogLibraryManager $xDialogLibraryManager
     */
    public function __construct(DialogLibraryManager $xDialogLibraryManager)
    {
        $this->xDialogLibraryManager = $xDialogLibraryManager;
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
            'args' => array_map(fn($xArg) => Parameter::make($xArg), $aArgs),
        ];
    }

    /**
     * Set the title of the next message.
     *
     * @param string $sTitle     The title of the message
     *
     * @return self
     */
    public function title(string $sTitle)
    {
        $this->sTitle = $sTitle;

        return $this;
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
        $sTitle = $this->sTitle;
        $this->sTitle = '';

        return [
            'lib' => $this->xDialogLibraryManager->getMessageLibrary()->getName(),
            'type' => $sType,
            'content' => [
                'title' => $sTitle,
                'phrase' => $this->phrase($sMessage, $aArgs),
            ],
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
            'lib' => $this->xDialogLibraryManager->getModalLibrary()->getName(),
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
            'lib' => $this->xDialogLibraryManager->getModalLibrary()->getName(),
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
            'lib' => $this->xDialogLibraryManager->getQuestionLibrary()->getName(),
            'phrase' => $this->phrase($sQuestion, $aArgs),
        ];
    }
}
