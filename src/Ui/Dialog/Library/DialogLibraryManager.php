<?php

/**
 * DialogLibraryManager.php - Shows alert and confirm dialogs
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Ui\Dialog\Library;

use Jaxon\Di\Container;
use Jaxon\Ui\Dialog\MessageInterface;
use Jaxon\Ui\Dialog\ModalInterface;
use Jaxon\Ui\Dialog\QuestionInterface;

class DialogLibraryManager
{
    /**
     * @var Container
     */
    private $di;

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
     * The constructor
     *
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
        // Library for javascript confirm and alert functions.
        $this->xAlertLibrary = new AlertLibrary();
    }

    /**
     * Set the QuestionInterface class name
     *
     * @param string $sQuestionLibrary    The QuestionInterface class name
     *
     * @return void
     */
    public function setQuestionLibrary(string $sQuestionLibrary)
    {
        $this->sQuestionLibrary = $sQuestionLibrary;
    }

    /**
     * Get the QuestionInterface instance
     *
     * @return QuestionInterface
     */
    public function getQuestionLibrary()
    {
        $sQuestionLibrary = $this->sNextLibrary ?: $this->sQuestionLibrary;
        return ($sQuestionLibrary) ? $this->di->g($sQuestionLibrary) : $this->xAlertLibrary;
    }

    /**
     * Set MessageInterface class name
     *
     * @param string $sMessageLibrary    The MessageInterface class name
     *
     * @return void
     */
    public function setMessageLibrary(string $sMessageLibrary)
    {
        $this->sMessageLibrary = $sMessageLibrary;
    }

    /**
     * Get the MessageInterface instance
     *
     * @return MessageInterface
     */
    public function getMessageLibrary(): MessageInterface
    {
        $sMessageLibrary = $this->sNextLibrary ?: $this->sMessageLibrary;
        return ($sMessageLibrary) ? $this->di->g($sMessageLibrary) : $this->xAlertLibrary;
    }

    /**
     * Set the ModalInterface class name
     *
     * @param string $sModalLibrary    The ModalInterface class name
     *
     * @return void
     */
    public function setModalLibrary(string $sModalLibrary)
    {
        $this->sModalLibrary = $sModalLibrary;
    }

    /**
     * Get the ModalInterface instance
     *
     * @return ModalInterface
     */
    public function getModalLibrary(): ?ModalInterface
    {
        $sModalLibrary = $this->sNextLibrary ?: $this->sModalLibrary;
        return ($sModalLibrary) ? $this->di->g($sModalLibrary) : null;
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
}
