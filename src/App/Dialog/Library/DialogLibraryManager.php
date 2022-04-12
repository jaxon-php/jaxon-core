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
use Jaxon\App\Dialog\MessageInterface;
use Jaxon\App\Dialog\ModalInterface;
use Jaxon\App\Dialog\QuestionInterface;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Config\Config;

use function array_map;
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
     * @var array
     */
    protected $aQuestionLibraries = [];

    /**
     * @var array
     */
    protected $aMessageLibraries = [];

    /**
     * @var array
     */
    protected $aModalLibraries = [];

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
     * Default javascript alert library
     *
     * @var AlertLibrary
     */
    private $xAlertLibrary;

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
        // Library for javascript confirm and alert functions.
        $this->xAlertLibrary = new AlertLibrary();
    }

    /**
     * Register a javascript dialog library adapter.
     *
     * @param string $sClassName
     * @param string $sName
     *
     * @return void
     * @throws SetupException
     */
    public function registerLibrary(string $sClassName, string $sName)
    {
        $bIsUsed = false;
        $aInterfaces = class_implements($sClassName);
        if(in_array(QuestionInterface::class, $aInterfaces))
        {
            $this->aQuestionLibraries[$sName] = $sClassName;
            $bIsUsed = true;
        }
        if(in_array(MessageInterface::class, $aInterfaces))
        {
            $this->aMessageLibraries[$sName] = $sClassName;
            $bIsUsed = true;
        }
        if(in_array(ModalInterface::class, $aInterfaces))
        {
            $this->aModalLibraries[$sName] = $sClassName;
            $bIsUsed = true;
        }

        if(!$bIsUsed)
        {
            // The class is invalid.
            $sMessage = $this->xTranslator->trans('errors.register.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }
        $this->aLibraries[] = $sClassName;
        $this->di->registerDialogLibrary($sClassName);
    }

    /**
     * Get the library class instances
     *
     * @return array
     */
    public function getLibraries(): array
    {
        return array_map(function($sClassName) {
            return $this->di->g($sClassName);
        }, $this->aLibraries);
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
        if(!isset($this->aQuestionLibraries[$sLibraryName]))
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
        $sLibraryName = $this->sNextLibrary ?: $this->sQuestionLibrary;
        return ($sLibraryName) ? $this->di->g($this->aQuestionLibraries[$sLibraryName]) : $this->xAlertLibrary;
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
        if(!isset($this->aMessageLibraries[$sLibraryName]))
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
        $sLibraryName = $this->sNextLibrary ?: $this->sMessageLibrary;
        return ($sLibraryName) ? $this->di->g($this->aMessageLibraries[$sLibraryName]) : $this->xAlertLibrary;
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
        if(!isset($this->aModalLibraries[$sLibraryName]))
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
        $sLibraryName = $this->sNextLibrary ?: $this->sModalLibrary;
        return ($sLibraryName) ? $this->di->g($this->aModalLibraries[$sLibraryName]) : null;
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
        $aLibraries = $this->xConfigManager->getOption('dialogs.libraries', []);
        foreach($aLibraries as $sClassName => $sName)
        {
            if(!$this->di->h($sClassName))
            {
                $this->registerLibrary($sClassName, $sName);
            }
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
        if(($sName = $this->xConfigManager->getOption('dialogs.default.modal', '')))
        {
            $this->setModalLibrary($sName);
        }
        // Set the default message library
        if(($sName = $this->xConfigManager->getOption('dialogs.default.message', '')))
        {
            $this->setMessageLibrary($sName);
        }
        // Set the default question library
        if(($sName = $this->xConfigManager->getOption('dialogs.default.question', '')))
        {
            $this->setQuestionLibrary($sName);
        }
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function onChanges(Config $xConfig)
    {
        // Reset the default libraries any time the config is changed.
        $this->registerLibraries();
        $this->setDefaultLibraries();
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function onChange(Config $xConfig, string $sName)
    {
        // Reset the default libraries any time the config is changed.
        if(substr($sName, 0, 15) === 'dialogs.default')
        {
            $this->setDefaultLibraries();
        }
    }
}
