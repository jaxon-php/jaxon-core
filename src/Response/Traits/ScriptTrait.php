<?php

/**
 * ScriptTrait.php
 *
 * Provides javascript related commands for the Response
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response\Traits;

use Jaxon\App\Dialog\DialogManager;
use Jaxon\Request\Js\Call;
use Jaxon\Response\ResponseInterface;
use Closure;
use JsonSerializable;

use function Jaxon\jaxon;
use function func_get_args;
use function array_shift;

trait ScriptTrait
{
     /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param string $sName    The command name
     * @param array|JsonSerializable $aOptions    The command options
     *
     * @return ResponseInterface
     */
    abstract public function addCommand(string $sName, array|JsonSerializable $aOptions): ResponseInterface;

    /**
     * Convert to string
     *
     * @param mixed $xData
     *
     * @return string
     */
    abstract protected function str($xData): string;

    /**
     * @return DialogManager
     */
    abstract protected function dialog(): DialogManager;

    /**
     * Add a command to call the specified javascript function with the given (optional) parameters
     *
     * @param string $sFunc    The name of the function to call
     *
     * @return ResponseInterface
     */
    public function call(string $sFunc): ResponseInterface
    {
        $aArgs = func_get_args();
        array_shift($aArgs);
        return $this->addCommand('script.call', ['func' => $this->str($sFunc),'args' => $aArgs]);
    }

    /**
     * Response command that prompts user with [ok] [cancel] style message box
     *
     * The provided closure will be called with a response object as unique parameter.
     * If the user clicks cancel, the response commands defined in the closure will be skipped.
     *
     * @param Closure $fCalls    A closure that defines the commands that can be skipped
     * @param string $sQuestion  The question to ask to the user
     * @param array $aArgs       The arguments for the placeholders in the question
     *
     * @return ResponseInterface
     */
    public function confirm(Closure $fCalls, string $sQuestion, array $aArgs = []): ResponseInterface
    {
        $xResponse = jaxon()->newResponse();
        $fCalls($xResponse);
        if(($nCommandCount = $xResponse->getCommandCount()) > 0)
        {
            $this->addCommand('script.confirm', [
                'count' => $nCommandCount,
                'question' => $this->dialog()->confirm($this->str($sQuestion), $aArgs),
            ]);

            // Append the provided commands
            $this->appendResponse($xResponse);
        }
        return $this;
    }

    /**
     * Add a command to display an alert message to the user
     *
     * @param string $sMessage    The message to be displayed
     * @param array $aArgs      The arguments for the placeholders in the message
     *
     * @return ResponseInterface
     */
    public function alert(string $sMessage, array $aArgs = []): ResponseInterface
    {
        $this->addCommand('dialog.message', $this->dialog()->info($this->str($sMessage), $aArgs));
        return $this;
    }

    /**
     * Add a command to display a debug message to the user
     *
     * @param string $sMessage    The message to be displayed
     *
     * @return ResponseInterface
     */
    public function debug(string $sMessage): ResponseInterface
    {
        return $this->addCommand('script.debug', ['message' => $this->str($sMessage)]);
    }

    /**
     * Add a command to ask the browser to navigate to the specified URL
     *
     * @param string $sURL    The relative or fully qualified URL
     * @param integer $nDelay    Number of seconds to delay before the redirect occurs
     *
     * @return ResponseInterface
     */
    public function redirect(string $sURL, int $nDelay = 0): ResponseInterface
    {
        return $this->addCommand('script.redirect', [
            'delay' => $nDelay,
            'url' => $this->xPluginManager->getParameterReader()->parseUrl($sURL),
        ]);
    }

    /**
     * Add a command to make Jaxon to pause execution of the response commands,
     * returning control to the browser so it can perform other commands asynchronously.
     *
     * After the specified delay, Jaxon will continue execution of the response commands.
     *
     * @param integer $tenths    The number of 1/10ths of a second to sleep
     *
     * @return ResponseInterface
     */
    public function sleep(int $tenths): ResponseInterface
    {
        return $this->addCommand('script.sleep', ['duration' => $tenths]);
    }

    /**
     * Add a command to set an event handler on the specified element
     * This handler can take custom parameters, and is is executed in a specific context.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param Call $xCall    The event handler
     *
     * @return ResponseInterface
     */
    public function setEventHandler(string $sTarget, string $sEvent, Call $xCall): ResponseInterface
    {
        return $this->addCommand('handler.event.set', [
            'id' => $this->str($sTarget),
            'event' => $this->str($sEvent),
            'func' => $xCall,
        ]);
    }

    /**
     * Add a command to set a click handler on the browser
     *
     * @param string $sTarget    The id of the element
     * @param Call $xCall    The event handler
     *
     * @return ResponseInterface
     */
    public function onClick(string $sTarget, Call $xCall): ResponseInterface
    {
        return $this->setEventHandler($sTarget, 'onclick', $xCall);
    }

    /**
     * Add a command to add an event handler on the specified element
     * This handler can take custom parameters, and is is executed in a specific context.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param Call $xCall    The event handler
     *
     * @return ResponseInterface
     */
    public function addEventHandler(string $sTarget, string $sEvent, Call $xCall): ResponseInterface
    {
        return $this->addCommand('handler.event.add', [
            'id' => $this->str($sTarget),
            'event' => $this->str($sEvent),
            'func' => $xCall,
        ]);
    }

    /**
     * Add a command to install an event handler on the specified element
     *
     * You can add more than one event handler to an element's event using this method.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param string $sHandler    The name of the javascript function to call when the event is fired
     *
     * @return ResponseInterface
     */
    public function addHandler(string $sTarget, string $sEvent, string $sHandler): ResponseInterface
    {
        return $this->addCommand('handler.add', [
            'id' => $this->str($sTarget),
            'event' => $this->str($sEvent),
            'func' => $this->str($sHandler),
        ]);
    }

    /**
     * Add a command to remove an event handler from an element
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param string $sHandler    The name of the javascript function called when the event is fired
     *
     * @return ResponseInterface
     */
    public function removeHandler(string $sTarget, string $sEvent, string $sHandler): ResponseInterface
    {
        return $this->addCommand('handler.remove', [
            'id' => $this->str($sTarget),
            'event' => $this->str($sEvent),
            'func' => $this->str($sHandler),
        ]);
    }
}
