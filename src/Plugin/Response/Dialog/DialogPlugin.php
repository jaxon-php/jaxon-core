<?php

/**
 * DialogPlugin.php - ModalInterface, message and question dialogs for Jaxon.
 *
 * Show modal, message and question dialogs with various javascript libraries
 * based on user settings.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-dialogs
 */

namespace Jaxon\Plugin\Response\Dialog;

use Jaxon\App\Dialog\DialogManager;
use Jaxon\App\Dialog\Library\DialogLibraryManager;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Response\ResponseInterface;

use function array_reduce;
use function trim;

class DialogPlugin extends ResponsePlugin
{
    /**
     * @const The plugin name
     */
    const NAME = 'dialog';

    /**
     * @var DialogManager
     */
    protected $xDialogManager;

    /**
     * @var DialogLibraryManager
     */
    protected $xLibraryManager;

    /**
     * @var array
     */
    protected $aLibraries = null;

    /**
     * The constructor
     *
     * @param DialogManager $xDialogManager
     * @param DialogLibraryManager $xLibraryManager
     */
    public function __construct(DialogManager $xDialogManager, DialogLibraryManager $xLibraryManager)
    {
        $this->xDialogManager = $xDialogManager;
        $this->xLibraryManager = $xLibraryManager;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        // The version number is used as hash
        return '4.0.0';
    }

    public function getUri(): string
    {
        return '';
    }

    public function getSubdir(): string
    {
        return '';
    }

    public function getVersion(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function setResponse(ResponseInterface $xResponse)
    {
        parent::setResponse($xResponse);

        // Hack the setResponse() method, to set the default libraries on each access to this plugin.
        $this->xLibraryManager->setNextLibrary('');
    }

    /**
     * Set the library to use for the next call.
     *
     * @param string $sLibrary The name of the library
     *
     * @return DialogPlugin
     */
    public function with(string $sLibrary): DialogPlugin
    {
        $this->xLibraryManager->setNextLibrary($sLibrary);
        return $this;
    }

    /**
     * @return array
     */
    private function getLibraries(): array
    {
        if($this->aLibraries === null)
        {
            $this->aLibraries = $this->xLibraryManager->getLibraries();
        }
        return $this->aLibraries;
    }

    /**
     * @inheritDoc
     */
    public function getJs(): string
    {
        return array_reduce($this->getLibraries(), function($sCode, $xLibrary) {
            return $sCode . $xLibrary->getJs() . "\n\n";
        }, '');
    }

    /**
     * @inheritDoc
     */
    public function getCss(): string
    {
        return array_reduce($this->getLibraries(), function($sCode, $xLibrary) {
            return $sCode . trim($xLibrary->getCss()) . "\n\n";
        }, '');
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function getScript(): string
    {
        return array_reduce($this->getLibraries(), function($sCode, $xLibrary) {
            return $sCode . trim($xLibrary->getScript()) . "\n\n";
        }, "jaxon.dialogs = {};\n");
    }

    /**
     * @inheritDoc
     */
    public function getReadyScript(): string
    {
        return array_reduce($this->getLibraries(), function($sCode, $xLibrary) {
            return $sCode . trim($xLibrary->getReadyScript()) . "\n\n";
        }, '');
    }

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
    public function show(string $sTitle, string $sContent, array $aButtons = [], array $aOptions = [])
    {
        // Show the modal dialog
        $this->addCommand('dialog.modal.show',
            $this->xDialogManager->show($sTitle, $sContent, $aButtons, $aOptions));
    }

    /**
     * Hide the modal dialog.
     *
     * @return void
     */
    public function hide()
    {
        // Hide the modal dialog
        $this->addCommand('dialog.modal.hide', $this->xDialogManager->hide());
    }

    /**
     * Set the title of the next message.
     *
     * @param string $sTitle     The title of the message
     *
     * @return DialogPlugin
     */
    public function title(string $sTitle): DialogPlugin
    {
        $this->xDialogManager->title($sTitle);
        return $this;
    }

    /**
     * Show a success message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return void
     */
    public function success(string $sMessage, array $aArgs = [])
    {
        $this->addCommand('dialog.message', $this->xDialogManager->success($sMessage, $aArgs));
    }

    /**
     * Show an information message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return void
     */
    public function info(string $sMessage, array $aArgs = [])
    {
        $this->addCommand('dialog.message', $this->xDialogManager->info($sMessage, $aArgs));
    }

    /**
     * Show a warning message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return void
     */
    public function warning(string $sMessage, array $aArgs = [])
    {
        $this->addCommand('dialog.message', $this->xDialogManager->warning($sMessage, $aArgs));
    }

    /**
     * Show an error message.
     *
     * @param string $sMessage  The text of the message
     * @param array $aArgs      The message arguments
     *
     * @return void
     */
    public function error(string $sMessage, array $aArgs = [])
    {
        $this->addCommand('dialog.message', $this->xDialogManager->error($sMessage, $aArgs));
    }
}
