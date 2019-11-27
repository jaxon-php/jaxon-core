<?php

/**
 * Dialog.php - Shows alert and confirm dialogs
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Dialogs;

use Jaxon\Contracts\Dialogs\Message as MessageContract;
use Jaxon\Contracts\Dialogs\Question as QuestionContract;

class Dialog
{
    /**
     * Javascript confirm function
     *
     * @var QuestionContract
     */
    private $xQuestion;

    /**
     * Default javascript confirm function
     *
     * @var QuestionContract
     */
    private $xDefaultQuestion;

    /**
     * Javascript alert function
     *
     * @var MessageContract
     */
    private $xMessage;

    /**
     * Default javascript alert function
     *
     * @var MessageContract
     */
    private $xDefaultMessage;

    /**
     * The constructor
     */
    public function __construct()
    {
        // Javascript confirm function
        $this->xQuestion = null;
        $this->xDefaultQuestion = new Question();

        // Javascript alert function
        $this->xMessage = null;
        $this->xDefaultMessage = new Message();
    }

    /**
     * Set the javascript confirm function
     *
     * @param QuestionContract         $xQuestion     The javascript confirm function
     *
     * @return void
     */
    public function setQuestion(QuestionContract $xQuestion)
    {
        $this->xQuestion = $xQuestion;
    }

    /**
     * Get the javascript question function
     *
     * @return QuestionContract
     */
    public function getQuestion()
    {
        return (($this->xQuestion) ? $this->xQuestion : $this->xDefaultQuestion);
    }

    /**
     * Get the default javascript confirm function
     *
     * @return QuestionContract
     */
    public function getDefaultQuestion()
    {
        return $this->xDefaultQuestion;
    }

    /**
     * Set the javascript alert function
     *
     * @param MessageContract           $xMessage       The javascript alert function
     *
     * @return void
     */
    public function setMessage(MessageContract $xMessage)
    {
        $this->xMessage = $xMessage;
    }

    /**
     * Get the javascript alert function
     *
     * @return MessageContract
     */
    public function getMessage()
    {
        return (($this->xMessage) ? $this->xMessage : $this->xDefaultMessage);
    }

    /**
     * Get the default javascript alert function
     *
     * @return Message
     */
    public function getDefaultMessage()
    {
        return $this->xDefaultMessage;
    }

    /**
     * Get the script which makes a call only if the user answers yes to the given question
     *
     * It is a function of the Question interface.
     *
     * @return string
     */
    public function confirm($question, $yesScript, $noScript)
    {
        return $this->getQuestion()->confirm($question, $yesScript, $noScript);
    }

    /**
     * Print a success message.
     *
     * It is a function of the Message interface.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return string|void
     */
    public function success($message, $title = null)
    {
        return $this->getMessage()->success($message, $title);
    }

    /**
     * Print an information message.
     *
     * It is a function of the Message interface.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return string|void
     */
    public function info($message, $title = null)
    {
        return $this->getMessage()->info($message, $title);
    }

    /**
     * Print a warning message.
     *
     * It is a function of the Message interface.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return string|void
     */
    public function warning($message, $title = null)
    {
        return $this->getMessage()->warning($message, $title);
    }

    /**
     * Print an error message.
     *
     * It is a function of the Message interface.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return string|void
     */
    public function error($message, $title = null)
    {
        return $this->getMessage()->error($message, $title);
    }
}
