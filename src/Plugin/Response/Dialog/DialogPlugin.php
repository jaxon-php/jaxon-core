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
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Response\Dialog;

use Jaxon\App\Dialog\AlertInterface;
use Jaxon\App\Dialog\ModalInterface;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AbstractResponsePlugin;

use function array_reduce;
use function trim;

class DialogPlugin extends AbstractResponsePlugin implements ModalInterface, AlertInterface
{
    /**
     * @const The plugin name
     */
    const NAME = 'dialog';

    /**
     * @var DialogCommand
     */
    protected $xDialogCommand;

    /**
     * @var DialogManager
     */
    protected $xDialogManager;

    /**
     * @var array
     */
    protected $aLibraries = null;

    /**
     * The constructor
     *
     * @param DialogCommand $xDialogCommand
     * @param DialogManager $xDialogManager
     */
    public function __construct(DialogCommand $xDialogCommand, DialogManager $xDialogManager)
    {
        $this->xDialogCommand = $xDialogCommand;
        $this->xDialogManager = $xDialogManager;
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

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->xDialogManager->setNextLibrary('');
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
        $this->xDialogManager->setNextLibrary($sLibrary);
        return $this;
    }

    /**
     * @return array
     */
    private function getLibraries(): array
    {
        if($this->aLibraries === null)
        {
            $this->aLibraries = $this->xDialogManager->getLibraries();
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
        }, '');
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
     * @inheritDoc
     */
    public function show(string $sTitle, string $sContent, array $aButtons = [], array $aOptions = [])
    {
        // Show the modal dialog
        $this->addCommand('dialog.modal.show',
            $this->xDialogCommand->show($sTitle, $sContent, $aButtons, $aOptions));
    }

    /**
     * @inheritDoc
     */
    public function hide()
    {
        // Hide the modal dialog
        $this->addCommand('dialog.modal.hide', $this->xDialogCommand->hide());
    }

    /**
     * @inheritDoc
     */
    public function title(string $sTitle): AlertInterface
    {
        $this->xDialogCommand->title($sTitle);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function success(string $sMessage, array $aArgs = [])
    {
        $this->addCommand('dialog.alert.show', $this->xDialogCommand->success($sMessage, $aArgs));
    }

    /**
     * @inheritDoc
     */
    public function info(string $sMessage, array $aArgs = [])
    {
        $this->addCommand('dialog.alert.show', $this->xDialogCommand->info($sMessage, $aArgs));
    }

    /**
     * @inheritDoc
     */
    public function warning(string $sMessage, array $aArgs = [])
    {
        $this->addCommand('dialog.alert.show', $this->xDialogCommand->warning($sMessage, $aArgs));
    }

    /**
     * @inheritDoc
     */
    public function error(string $sMessage, array $aArgs = [])
    {
        $this->addCommand('dialog.alert.show', $this->xDialogCommand->error($sMessage, $aArgs));
    }
}
