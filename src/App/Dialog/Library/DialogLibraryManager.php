<?php

/**
 * DialogLibraryManager.php
 *
 * Manage dialog library list and defaults.
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog\Library;

use Jaxon\App\Config\ConfigListenerInterface;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\LibraryInterface;
use Jaxon\App\Dialog\MessageInterface;
use Jaxon\App\Dialog\ModalInterface;
use Jaxon\App\Dialog\QuestionInterface;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Config\Config;

use function array_map;
use function array_keys;
use function class_implements;
use function in_array;
use function substr;

class DialogLibraryManager implements ConfigListenerInterface
{
    /**
     * @var Container
     */
    private $di;

    /**
     * @var array
     */
    protected $aLibraries = [];

    /**
     * The QuestionInterface class name
     *
     * @var string
     */
    private $sQuestionLibrary = '';

    /**
     * The MessageInterface class name
     *
     * @var string
     */
    private $sMessageLibrary = '';

    /**
     * The ModalInterface class name
     *
     * @var string
     */
    private $sModalLibrary = '';

    /**
     * The name of the library to use for the next call.
     * This is used to override the default library.
     *
     * @var string
     */
    protected $sNextLibrary = '';

    /**
     * @var ConfigManager
     */
    protected $xConfigManager;

    /**
     * @var Translator
     */
    private $xTranslator;

    /**
     * The constructor
     *
     * @param Container $di
     * @param ConfigManager $xConfigManager
     * @param Translator $xTranslator
     */
    public function __construct(Container $di, ConfigManager $xConfigManager, Translator $xTranslator)
    {
        $this->di = $di;
        $this->xConfigManager = $xConfigManager;
        $this->xTranslator = $xTranslator;
    }

    /**
     * Register a javascript dialog library adapter.
     *
     * @param string $sClassName
     * @param string $sLibraryName
     *
     * @return void
     * @throws SetupException
     */
    public function registerLibrary(string $sClassName, string $sLibraryName)
    {
        if(isset($this->aLibraries[$sLibraryName]))
        {
            return;
        }
        if(!($aInterfaces = class_implements($sClassName)))
        {
            // The class is invalid.
            $sMessage = $this->xTranslator->trans('errors.register.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }

        $bIsQuestion = in_array(QuestionInterface::class, $aInterfaces);
        $bIsMessage = in_array(MessageInterface::class, $aInterfaces);
        $bIsModal = in_array(ModalInterface::class, $aInterfaces);
        if(!$bIsQuestion && !$bIsMessage && !$bIsModal)
        {
            // The class is invalid.
            $sMessage = $this->xTranslator->trans('errors.register.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }

        // Save the library
        $this->aLibraries[$sLibraryName] = [
            'question' => $bIsQuestion,
            'message' => $bIsMessage,
            'modal' => $bIsModal,
            'used' => false,
        ];
        // Register the library class in the container
        $this->di->registerDialogLibrary($sClassName, $sLibraryName);
    }

    /**
     * Set the QuestionInterface library
     *
     * @param string $sLibraryName The QuestionInterface library name
     *
     * @return void
     * @throws SetupException
     */
    public function setQuestionLibrary(string $sLibraryName)
    {
        if(!isset($this->aLibraries[$sLibraryName]) || !$this->aLibraries[$sLibraryName]['question'])
        {
            $sMessage = $this->xTranslator->trans('errors.dialog.library',
                ['type' => 'question', 'name' => $sLibraryName]);
            throw new SetupException($sMessage);
        }
        $this->sQuestionLibrary = $sLibraryName;
    }

    /**
     * Get the QuestionInterface library
     *
     * @return QuestionInterface
     */
    public function getQuestionLibrary(): QuestionInterface
    {
        return $this->di->getQuestionLibrary($this->sNextLibrary ?: $this->sQuestionLibrary);
    }

    /**
     * Set MessageInterface library
     *
     * @param string $sLibraryName The MessageInterface library name
     *
     * @return void
     * @throws SetupException
     */
    public function setMessageLibrary(string $sLibraryName)
    {
        if(!isset($this->aLibraries[$sLibraryName]) || !$this->aLibraries[$sLibraryName]['message'])
        {
            $sMessage = $this->xTranslator->trans('errors.dialog.library',
                ['type' => 'message', 'name' => $sLibraryName]);
            throw new SetupException($sMessage);
        }
        $this->sMessageLibrary = $sLibraryName;
    }

    /**
     * Get the MessageInterface library
     *
     * @return MessageInterface
     */
    public function getMessageLibrary(): MessageInterface
    {
        return $this->di->getMessageLibrary($this->sNextLibrary ?: $this->sMessageLibrary);
    }

    /**
     * Set the ModalInterface library
     *
     * @param string $sLibraryName The ModalInterface library name
     *
     * @return void
     * @throws SetupException
     */
    public function setModalLibrary(string $sLibraryName)
    {
        if(!isset($this->aLibraries[$sLibraryName]) || !$this->aLibraries[$sLibraryName]['modal'])
        {
            $sMessage = $this->xTranslator->trans('errors.dialog.library',
                ['type' => 'modal', 'name' => $sLibraryName]);
            throw new SetupException($sMessage);
        }
        $this->sModalLibrary = $sLibraryName;
    }

    /**
     * Get the ModalInterface library
     *
     * @return ModalInterface
     */
    public function getModalLibrary(): ?ModalInterface
    {
        return $this->di->getModalLibrary($this->sNextLibrary ?: $this->sModalLibrary);
    }

    /**
     * Set the name of the library to use for the next call
     *
     * @param string $sNextLibrary
     *
     * @return void
     */
    public function setNextLibrary(string $sNextLibrary): void
    {
        $this->sNextLibrary = $sNextLibrary;
    }

    /**
     * Register the javascript dialog libraries from config options.
     *
     * @return void
     * @throws SetupException
     */
    protected function registerLibraries()
    {
        $aLibraries = $this->xConfigManager->getOption('dialogs.lib.ext', []);
        foreach($aLibraries as $sLibraryName => $sClassName)
        {
            $this->registerLibrary($sClassName, $sLibraryName);
        }
    }

    /**
     * Set the default library for each dialog feature.
     *
     * @return void
     * @throws SetupException
     */
    protected function setDefaultLibraries()
    {
        // Set the default modal library
        if(($sLibraryName = $this->xConfigManager->getOption('dialogs.default.modal', '')))
        {
            $this->setModalLibrary($sLibraryName);
            $this->aLibraries[$sLibraryName]['used'] = true;
        }
        // Set the default message library
        if(($sLibraryName = $this->xConfigManager->getOption('dialogs.default.message', '')))
        {
            $this->setMessageLibrary($sLibraryName);
            $this->aLibraries[$sLibraryName]['used'] = true;
        }
        // Set the default question library
        if(($sLibraryName = $this->xConfigManager->getOption('dialogs.default.question', '')))
        {
            $this->setQuestionLibrary($sLibraryName);
            $this->aLibraries[$sLibraryName]['used'] = true;
        }
    }

    /**
     * Set the libraries in use.
     *
     * @return void
     */
    protected function setUsedLibraries()
    {
        // Set the other libraries in use
        $aLibraries = $this->xConfigManager->getOption('dialogs.lib.use', []);
        foreach($aLibraries as $sLibraryName)
        {
            if(isset($this->aLibraries[$sLibraryName])) // Make sure the library exists
            {
                $this->aLibraries[$sLibraryName]['used'] = true;
            }
        }
    }

    /**
     * Get the dialog libraries class instances
     *
     * @return LibraryInterface[]
     */
    public function getLibraries(): array
    {
        // Only return the libraries that are used.
        $aLibraries = array_filter($this->aLibraries, function($aLibrary) {
            return $aLibrary['used'];
        });
        return array_map(function($sLibraryName) {
            return $this->di->getDialogLibrary($sLibraryName);
        }, array_keys($aLibraries));
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function onChange(Config $xConfig, string $sName)
    {
        if($sName === '')
        {
            // Reset the default libraries any time the config is changed.
            $this->registerLibraries();
            $this->setDefaultLibraries();
            $this->setUsedLibraries();
            return;
        }
        $sPrefix = substr($sName, 0, 15);
        switch($sPrefix)
        {
        case 'dialogs.default':
            $this->setDefaultLibraries();
            return;
        case 'dialogs.lib.ext':
            $this->registerLibraries();
            return;
        case 'dialogs.lib.use':
            $this->setUsedLibraries();
            return;
        default:
        }
    }
}
