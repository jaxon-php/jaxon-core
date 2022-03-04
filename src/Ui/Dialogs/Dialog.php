<?php

/**
 * Dialog.php - Shows alert and confirm dialogs
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Ui\Dialogs;

use Jaxon\Jaxon;
use Jaxon\Contracts\Dialogs\Message as MessageContract;
use Jaxon\Contracts\Dialogs\Question as QuestionContract;

class Dialog
{
    /**
     * @var Jaxon
     */
    private $jaxon;

    /**
     * The QuestionContract class name (javascript confirm function)
     *
     * @var string
     */
    private $sQuestion = '';

    /**
     * Default javascript confirm function
     *
     * @var QuestionContract
     */
    private $xDefaultQuestion;

    /**
     * The MessageContract class name (javascript alert function)
     *
     * @var string
     */
    private $sMessage = '';

    /**
     * Default javascript alert function
     *
     * @var MessageContract
     */
    private $xDefaultMessage;

    /**
     * The constructor
     *
     * @param Jaxon $jaxon
     */
    public function __construct(Jaxon $jaxon)
    {
        $this->jaxon = $jaxon;
        // Javascript confirm function
        $this->xDefaultQuestion = new Question();
        // Javascript alert function
        $this->xDefaultMessage = new Message();
    }

    /**
     * Set the QuestionContract class name
     *
     * @param string $sQuestion     The QuestionContract class name
     *
     * @return void
     */
    public function setQuestion(string $sQuestion)
    {
        $this->sQuestion = $sQuestion;
    }

    /**
     * Get the QuestionContract class name (javascript question function)
     *
     * @return QuestionContract
     */
    public function getQuestion()
    {
        return ($this->sQuestion) ? $this->jaxon->di()->get($this->sQuestion) : $this->xDefaultQuestion;
    }

    /**
     * Get the default QuestionContract class name (javascript confirm function)
     *
     * @return QuestionContract
     */
    public function getDefaultQuestion(): QuestionContract
    {
        return $this->xDefaultQuestion;
    }

    /**
     * Set MessageContract class name
     *
     * @param string $sMessage       The MessageContract class name
     *
     * @return void
     */
    public function setMessage(string $sMessage)
    {
        $this->sMessage = $sMessage;
    }

    /**
     * Get the MessageContract class name (javascript alert function)
     *
     * @return MessageContract
     */
    public function getMessage(): MessageContract
    {
        return ($this->sMessage) ? $this->jaxon->di()->get($this->sMessage) : $this->xDefaultMessage;
    }

    /**
     * Get the default MessageContract class name (javascript alert function)
     *
     * @return MessageContract
     */
    public function getDefaultMessage(): MessageContract
    {
        return $this->xDefaultMessage;
    }

    /**
     * Get the script which makes a call only if the user answers yes to the given question.
     * It is a function of the Question interface.
     *
     * @param string            $sQuestion
     * @param string            $sYesScript
     * @param string            $sNoScript
     *
     * @return string
     */
    public function confirm(string $sQuestion, string $sYesScript, string $sNoScript): string
    {
        return $this->getQuestion()->confirm($sQuestion, $sYesScript, $sNoScript);
    }

    /**
     * Print a success message.
     *
     * It is a function of the Message interface.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return string
     */
    public function success(string $sMessage, string $sTitle = ''): string
    {
        return $this->getMessage()->success($sMessage, $sTitle);
    }

    /**
     * Print an information message.
     *
     * It is a function of the Message interface.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return string
     */
    public function info(string $sMessage, string $sTitle = ''): string
    {
        return $this->getMessage()->info($sMessage, $sTitle);
    }

    /**
     * Print a warning message.
     *
     * It is a function of the Message interface.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return string
     */
    public function warning(string $sMessage, string $sTitle = ''): string
    {
        return $this->getMessage()->warning($sMessage, $sTitle);
    }

    /**
     * Print an error message.
     *
     * It is a function of the Message interface.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return string
     */
    public function error(string $sMessage, string $sTitle = ''): string
    {
        return $this->getMessage()->error($sMessage, $sTitle);
    }
}
