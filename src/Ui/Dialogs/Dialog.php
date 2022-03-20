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

use Jaxon\Container\Container;

class Dialog
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
    private $sQuestion = '';

    /**
     * Default javascript confirm function
     *
     * @var QuestionInterface
     */
    private $xDefaultQuestion;

    /**
     * The MessageInterface class name (javascript alert function)
     *
     * @var string
     */
    private $sMessage = '';

    /**
     * Default javascript alert function
     *
     * @var MessageInterface
     */
    private $xDefaultMessage;

    /**
     * The constructor
     *
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
        // Javascript confirm function
        $this->xDefaultQuestion = new Question();
        // Javascript alert function
        $this->xDefaultMessage = new Message();
    }

    /**
     * Set the QuestionInterface class name
     *
     * @param string $sQuestion    The QuestionInterface class name
     *
     * @return void
     */
    public function setQuestion(string $sQuestion)
    {
        $this->sQuestion = $sQuestion;
    }

    /**
     * Get the QuestionInterface class name (javascript question function)
     *
     * @return QuestionInterface
     */
    public function getQuestion()
    {
        return ($this->sQuestion) ? $this->di->get($this->sQuestion) : $this->xDefaultQuestion;
    }

    /**
     * Get the default QuestionInterface class name (javascript confirm function)
     *
     * @return QuestionInterface
     */
    public function getDefaultQuestion(): QuestionInterface
    {
        return $this->xDefaultQuestion;
    }

    /**
     * Set MessageInterface class name
     *
     * @param string $sMessage    The MessageInterface class name
     *
     * @return void
     */
    public function setMessage(string $sMessage)
    {
        $this->sMessage = $sMessage;
    }

    /**
     * Get the MessageInterface class name (javascript alert function)
     *
     * @return MessageInterface
     */
    public function getMessage(): MessageInterface
    {
        return ($this->sMessage) ? $this->di->get($this->sMessage) : $this->xDefaultMessage;
    }

    /**
     * Get the default MessageInterface class name (javascript alert function)
     *
     * @return MessageInterface
     */
    public function getDefaultMessage(): MessageInterface
    {
        return $this->xDefaultMessage;
    }

    /**
     * Get the script which makes a call only if the user answers yes to the given question.
     * It is a function of the Question interface.
     *
     * @param string  $sQuestion
     * @param string  $sYesScript
     * @param string  $sNoScript
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
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
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
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
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
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
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
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return string
     */
    public function error(string $sMessage, string $sTitle = ''): string
    {
        return $this->getMessage()->error($sMessage, $sTitle);
    }
}
