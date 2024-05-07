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

use Jaxon\Response\ResponseInterface;
use JsonSerializable;

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
     * Response command that prompts user with [ok] [cancel] style message box
     *
     * If the user clicks cancel, the specified number of response commands
     * following this one, will be skipped.
     *
     * @param integer $nCommandCount    The number of commands to skip upon cancel
     * @param string $sQuestion    The message to display to the user
     *
     * @return ResponseInterface
     */
    public function confirmCommands(int $nCommandCount, string $sQuestion): ResponseInterface
    {
        return $this->addCommand('script.confirm', [
            'count' => $nCommandCount,
            'question' => $this->str($sQuestion),
        ]);
    }

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
     * Add a command to display an alert message to the user
     *
     * @param string $sMessage    The message to be displayed
     *
     * @return ResponseInterface
     */
    public function alert(string $sMessage): ResponseInterface
    {
        return $this->addCommand('script.alert', ['message' => $this->str($sMessage)]);
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
     * @param array $aCall    The event handler
     *
     * @return ResponseInterface
     */
    public function setEventHandler(string $sTarget, string $sEvent, array $aCall): ResponseInterface
    {
        return $this->addCommand('handler.event.set', [
            'id' => $this->str($sTarget),
            'event' => $this->str($sEvent),
            'call' => $aCall,
        ]);
    }

    /**
     * Add a command to set a click handler on the browser
     *
     * @param string $sTarget    The id of the element
     * @param array $aCall    The event handler
     *
     * @return ResponseInterface
     */
    public function onClick(string $sTarget, array $aCall): ResponseInterface
    {
        return $this->setEventHandler($sTarget, 'onclick', $aCall);
    }

    /**
     * Add a command to add an event handler on the specified element
     * This handler can take custom parameters, and is is executed in a specific context.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param array $aCall    The event handler
     *
     * @return ResponseInterface
     */
    public function addEventHandler(string $sTarget, string $sEvent, array $aCall): ResponseInterface
    {
        return $this->addCommand('handler.event.add', [
            'id' => $this->str($sTarget),
            'event' => $this->str($sEvent),
            'call' => $aCall,
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
