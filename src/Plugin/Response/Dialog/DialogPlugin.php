<?php

/**
 * DialogPlugin.php - Modal, alert and confirm dialogs for Jaxon.
 *
 * Show modal, alert and confirm dialogs with various javascript libraries.
 * This class implements the dialog commands.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Response\Dialog;

use Jaxon\App\Dialog\AlertInterface;
use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\App\Dialog\ModalInterface;
use Jaxon\Plugin\PluginInterface;
use Jaxon\Plugin\ResponsePluginInterface;
use Jaxon\Plugin\ResponsePluginTrait;

class DialogPlugin implements PluginInterface, ResponsePluginInterface, ModalInterface, AlertInterface
{
    use ResponsePluginTrait;

    /**
     * @const The plugin name
     */
    public const NAME = 'dialog';

    /**
     * The constructor
     *
     * @param DialogCommand $xDialogCommand
     */
    public function __construct(private DialogCommand $xDialogCommand)
    {}

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    protected function init(): void
    {}

    /**
     * Set the library to use for the next call.
     *
     * @param string $sLibrary The name of the library
     *
     * @return DialogPlugin
     */
    public function with(string $sLibrary): DialogPlugin
    {
        $this->xDialogCommand->library($sLibrary);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function show(string $sTitle, string $sContent, array $aButtons = [], array $aOptions = []): void
    {
        // Show the modal dialog
        $this->addCommand('dialog.modal.show', $this->xDialogCommand
            ->show($sTitle, $sContent, $aButtons, $aOptions));
    }

    /**
     * @inheritDoc
     */
    public function hide(): void
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
    public function success(string $sMessage, ...$aArgs): void
    {
        $this->addCommand('dialog.alert.show', $this->xDialogCommand
            ->success($sMessage, $aArgs));
    }

    /**
     * @inheritDoc
     */
    public function info(string $sMessage, ...$aArgs): void
    {
        $this->addCommand('dialog.alert.show', $this->xDialogCommand
            ->info($sMessage, $aArgs));
    }

    /**
     * @inheritDoc
     */
    public function warning(string $sMessage, ...$aArgs): void
    {
        $this->addCommand('dialog.alert.show', $this->xDialogCommand
            ->warning($sMessage, $aArgs));
    }

    /**
     * @inheritDoc
     */
    public function error(string $sMessage, ...$aArgs): void
    {
        $this->addCommand('dialog.alert.show', $this->xDialogCommand
            ->error($sMessage, $aArgs));
    }
}
