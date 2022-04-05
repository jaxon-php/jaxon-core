<?php

/**
 * DialogFacade.php - Shows alert and confirm dialogs
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Ui\Dialogs;

use Jaxon\Di\Container;
use Jaxon\Response\Response;

class DialogFacade
{
    /**
     * @var Container
     */
    private $di;

    /**
     * The QuestionInterface class name (javascript confirm function)
     *
     * @var string
     */
    private $sQuestionLibrary = '';

    /**
     * The MessageInterface class name (javascript alert function)
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
        // Javascript confirm function
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
     * Get the QuestionInterface instance (javascript question function)
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
     * Get the MessageInterface instance (javascript alert function)
     *
     * @param bool $bReturn Whether to return the code
     * @param Response|null $xResponse
     * @param string $sMessageLibrary
     *
     * @return MessageInterface
     */
    public function getMessageLibrary(bool $bReturn, ?Response $xResponse = null, string $sMessageLibrary = ''): MessageInterface
    {
        if($sMessageLibrary === '')
        {
            $sMessageLibrary = $this->sMessageLibrary;
        }
        $xLibrary = ($sMessageLibrary) ? $this->di->g($sMessageLibrary) : $this->xAlertLibrary;
        $xLibrary->setReturn($bReturn);
        if($xResponse !== null)
        {
            $xLibrary->setResponse($xResponse);
        }
        return $xLibrary;
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
     * Get the ModalInterface instance (javascript question function)
     *
     * @param Response|null $xResponse
     * @param string $sModalLibrary
     *
     * @return ModalInterface
     */
    public function getModalLibrary(?Response $xResponse = null, string $sModalLibrary = ''): ?ModalInterface
    {
        if($sModalLibrary === '')
        {
            $sModalLibrary = $this->sModalLibrary;
        }
        $xLibrary = ($sModalLibrary) ? $this->di->g($sModalLibrary) : null;
        if($xResponse !== null)
        {
            $xLibrary->setResponse($xResponse);
        }
        return $xLibrary;
    }
}
