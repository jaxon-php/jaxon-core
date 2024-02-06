<?php

/**
 * JsTrait.php - Provides javascript related commands for the Response
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response\Traits;

use Jaxon\Response\ResponseInterface;

use function func_get_args;
use function array_shift;

trait JsTrait
{
    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param array $aAttributes    Associative array of attributes that will describe the command
     * @param mixed $mData    The data to be associated with this command
     *
     * @return ResponseInterface
     */
    abstract public function addCommand(array $aAttributes, $mData): ResponseInterface;

    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param string $sName    The command name
     * @param array $aAttributes    Associative array of attributes that will describe the command
     * @param mixed $mData    The data to be associated with this command
     * @param bool $bRemoveEmpty    If true, remove empty attributes
     *
     * @return ResponseInterface
     */
    abstract protected function _addCommand(string $sName, array $aAttributes,
        $mData, bool $bRemoveEmpty = false): ResponseInterface;

    /**
     * Response command that prompts user with [ok] [cancel] style message box
     *
     * If the user clicks cancel, the specified number of response commands
     * following this one, will be skipped.
     *
     * @param integer $nCommandCount    The number of commands to skip upon cancel
     * @param string $sMessage    The message to display to the user
     *
     * @return ResponseInterface
     */
    public function confirmCommands(int $nCommandCount, string $sMessage): ResponseInterface
    {
        $aAttributes = ['count' => $nCommandCount];
        return $this->_addCommand('cc', $aAttributes, $sMessage);
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
        return $this->_addCommand('al', [], $sMessage);
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
        return $this->_addCommand('dbg', [], $sMessage);
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
        $sURL = $this->xPluginManager->getParameterReader()->parseUrl($sURL);
        return $this->_addCommand('rd', ['delay' => $nDelay], $sURL);
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
        $aAttributes = ['cmd' => 'jc', 'func' => $sFunc];
        return $this->addCommand($aAttributes, $aArgs);
    }

    /**
     * Add a command to set a click handler on the browser
     *
     * @param string $sTarget    The id of the element that contains the event
     * @param string $sScript    The javascript to execute when the event is fired
     *
     * @return ResponseInterface
     */
    public function onClick(string $sTarget, string $sScript): ResponseInterface
    {
        return $this->setEvent($sTarget, 'onclick', $sScript);
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
        $aAttributes = ['id' => $sTarget, 'prop' => $sEvent];
        return $this->_addCommand('ah', $aAttributes, $sHandler);
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
        $aAttributes = ['id' => $sTarget, 'prop' => $sEvent];
        return $this->_addCommand('rh', $aAttributes, $sHandler);
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
        $aAttributes = ['cmd' =>'s', 'prop' => $tenths];
        return $this->addCommand($aAttributes, '');
    }
}
