<?php

/**
 * DialogPlugin.php - ModalInterface, message and question dialogs for Jaxon.
 *
 * Show modal, message and question dialogs with various javascript libraries
 * based on user settings.
 *
 * @package jaxon-dialogs
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-dialogs
 */

namespace Jaxon\Response\Plugin\Dialog;

use Jaxon\Config\ConfigManager;
use Jaxon\Di\Container;
use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Ui\Dialog\Library\DialogLibraryManager;
use Jaxon\Ui\Dialog\MessageInterface;
use Jaxon\Ui\Dialog\ModalInterface;
use Jaxon\Ui\Dialog\QuestionInterface;

use function array_reduce;

class DialogPlugin extends ResponsePlugin implements ModalInterface, MessageInterface, QuestionInterface
{
    /**
     * @const The plugin name
     */
    const NAME = 'dialog';

    /**
     * Dependency Injection manager
     *
     * @var Container
     */
    protected $di;

    /**
     * @var DialogLibraryManager
     */
    protected $xLibraryManager;

    /**
     * @var ConfigManager
     */
    protected $xConfigManager;

    /**
     * The name of the library to use for the next call
     *
     * @var string
     */
    protected $sNextLibrary = '';

    /**
     * @vr array
     */
    protected $aLibraries = [];

    /**
     * The constructor
     *
     * @param Container $di
     * @param ConfigManager $xConfigManager
     * @param DialogLibraryManager $xLibraryManager
     */
    public function __construct(Container $di, ConfigManager $xConfigManager, DialogLibraryManager $xLibraryManager)
    {
        $this->di = $di;
        $this->xConfigManager = $xConfigManager;
        $this->xLibraryManager = $xLibraryManager;

        $this->registerLibraries();
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

    /**
     * Register the javascript libraries adapters in the DI container.
     *
     * @return void
     */
    protected function registerLibraries()
    {
        $aLibraries = $this->xConfigManager->getOption('dialogs.libraries', []);
        foreach($aLibraries as $sName => $sClassName)
        {
            $this->aLibraries[] = $sName;
            $this->di->registerDialogLibrary($sClassName, $sName);
        }

        // Get the default modal library
        if(($sName = $this->xConfigManager->getOption('dialogs.default.modal', '')))
        {
            $this->xLibraryManager->setModalLibrary($sName);
        }
        // Get the configured message library
        if(($sName = $this->xConfigManager->getOption('dialogs.default.message', '')))
        {
            $this->xLibraryManager->setMessageLibrary($sName);
        }
        // Get the configured question library
        if(($sName = $this->xConfigManager->getOption('dialogs.default.question', '')))
        {
            $this->xLibraryManager->setQuestionLibrary($sName);
        }
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
        $this->sNextLibrary = $sLibrary;
        return $this;
    }

    /**
     * Get the library adapter to use for modals.
     *
     * @return ModalInterface|null
     */
    protected function getModalLibrary(): ?ModalInterface
    {
        $xLibrary = $this->xLibraryManager->getModalLibrary($this->sNextLibrary);
        $xLibrary->setResponse($this->xResponse);
        $this->sNextLibrary = '';
        return $xLibrary;
    }

    /**
     * Get the library adapter to use for messages.
     *
     * @return MessageInterface|null
     */
    protected function getMessageLibrary(): ?MessageInterface
    {
        $xLibrary = $this->xLibraryManager->getMessageLibrary($this->sNextLibrary);
        $xLibrary->setResponse($this->xResponse);
        // By default, always add commands to the response
        $xLibrary->setReturnCode(false);
        $this->sNextLibrary = '';
        return $xLibrary;
    }

    /**
     * Get the library adapter to use for question.
     *
     * @return QuestionInterface|null
     */
    protected function getQuestionLibrary(): ?QuestionInterface
    {
        $xLibrary = $this->xLibraryManager->getQuestionLibrary($this->sNextLibrary);
        $this->sNextLibrary = '';
        return $xLibrary;
    }

    /**
     * @inheritDoc
     */
    public function getJs(): string
    {
        return array_reduce($this->aLibraries, function($sCode, $sName) {
            $xLibrary = $this->di->g($sName);
            return $sCode . $xLibrary->getJs() . "\n\n";
        }, '');
    }

    /**
     * @inheritDoc
     */
    public function getCss(): string
    {
        return array_reduce($this->aLibraries, function($sCode, $sName) {
            $xLibrary = $this->di->g($sName);
            return $sCode . $xLibrary->getCss() . "\n\n";
        }, '');
    }

    /**
     * @inheritDoc
     */
    public function getScript(): string
    {
        if(empty($this->aLibraries))
        {
            return ''; // Do not return anything if no dialog library is registered.
        }
        return array_reduce($this->aLibraries, function($sCode, $sName) {
            $xLibrary = $this->di->g($sName);
            return $sCode . $xLibrary->getScript() . "\n\n";
        }, "jaxon.dialogs = {};\n");
    }

    /**
     * @inheritDoc
     */
    public function getReadyScript(): string
    {
        return array_reduce($this->aLibraries, function($sCode, $sName) {
            $xLibrary = $this->di->g($sName);
            return $sCode . $xLibrary->getReadyScript() . "\n\n";
        }, '');
    }

    /**
     * Show a modal dialog.
     *
     * It is a function of the Jaxon\Dialogs\Contracts\ModalInterface interface.
     *
     * @param string $sTitle The title of the dialog
     * @param string $sContent The content of the dialog
     * @param array $aButtons The buttons of the dialog
     * @param array $aOptions The options of the dialog
     *
     * Each button is an array containin the following entries:
     * - title: the text to be printed in the button
     * - class: the CSS class of the button
     * - click: the javascript function to be called when the button is clicked
     * If the click value is set to "close", then the buttons closes the dialog.
     *
     * The content of the $aOptions depends on the javascript library in use.
     * Check their specific documentation for more information.
     *
     * @return void
     */
    public function show(string $sTitle, string $sContent, array $aButtons = [], array $aOptions = [])
    {
        $this->getModalLibrary()->show($sTitle, $sContent, $aButtons, $aOptions);
    }

    /**
     * Show a modal dialog.
     *
     * It is another name for the show() function.
     *
     * @param string $sTitle The title of the dialog
     * @param string $sContent The content of the dialog
     * @param array $aButtons The buttons of the dialog
     * @param array $aOptions The options of the dialog
     *
     * @return void
     */
    public function modal(string $sTitle, string $sContent, array $aButtons = [], array $aOptions = [])
    {
        $this->show($sTitle, $sContent, $aButtons, $aOptions);
    }

    /**
     * Hide the modal dialog.
     *
     * It is a function of the Jaxon\Dialogs\Contracts\ModalInterface interface.
     *
     * @return void
     */
    public function hide()
    {
        $this->getModalLibrary()->hide();
    }

    /**
     * @inheritDoc
     */
    public function success(string $sMessage, string $sTitle = ''): string
    {
        return $this->getMessageLibrary()->success($sMessage, $sTitle);
    }

    /**
     * @inheritDoc
     */
    public function info(string $sMessage, string $sTitle = ''): string
    {
        return $this->getMessageLibrary()->info($sMessage, $sTitle);
    }

    /**
     * @inheritDoc
     */
    public function warning(string $sMessage, string $sTitle = ''): string
    {
        return $this->getMessageLibrary()->warning($sMessage, $sTitle);
    }

    /**
     * @inheritDoc
     */
    public function error(string $sMessage, string $sTitle = ''): string
    {
        return $this->getMessageLibrary()->error($sMessage, $sTitle);
    }

    /**
     * @inheritDoc
     */
    public function confirm(string $sQuestion, string $sYesScript, string $sNoScript): string
    {
        return $this->getQuestionLibrary()->confirm($sQuestion, $sYesScript, $sNoScript);
    }
}
