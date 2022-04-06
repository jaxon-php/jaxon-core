<?php

/**
 * DialogFacade.php - Shows alert and confirm dialogs
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
     * @param string $sQuestionLibrary
     *
     * @return QuestionInterface
     */
    public function getQuestionLibrary(string $sQuestionLibrary = '')
    {
        if($sQuestionLibrary === '')
        {
            $sQuestionLibrary = $this->sQuestionLibrary;
        }
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
     * @param string $sMessageLibrary
     *
     * @return MessageInterface
     */
    public function getMessageLibrary(string $sMessageLibrary = ''): MessageInterface
    {
        if($sMessageLibrary === '')
        {
            $sMessageLibrary = $this->sMessageLibrary;
        }
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
     * @param string $sModalLibrary
     *
     * @return ModalInterface
     */
    public function getModalLibrary(string $sModalLibrary = ''): ?ModalInterface
    {
        if($sModalLibrary === '')
        {
            $sModalLibrary = $this->sModalLibrary;
        }
        return ($sModalLibrary) ? $this->di->g($sModalLibrary) : null;
    }
}
