<?php

namespace Jaxon\Response;

use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Request\Call\JsCall;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use JsonSerializable;

interface ResponseInterface
{
    /**
     * Get the content type, which is always set to 'text/json'
     *
     * @return string
     */
    public function getContentType(): string;

    /**
     * Get the commands in the response
     *
     * @return array
     */
    public function getCommands(): array;

    /**
     * Get the number of commands in the response
     *
     * @return int
     */
    public function getCommandCount(): int;

    /**
     * Clear all the commands already added to the response
     *
     * @return void
     */
    public function clearCommands();

    /**
     * Merge the commands with those in this <Response> object
     *
     * @param array $aCommands    The commands to merge
     * @param bool $bBefore    Add the new commands to the beginning of the list
     *
     * @return void
     */
    public function appendCommands(array $aCommands, bool $bBefore = false);

    /**
     * Merge the response commands with those in this <Response> object
     *
     * @param ResponseInterface    The <Response> object
     * @param bool $bBefore    Add the new commands to the beginning of the list
     *
     * @return void
     */
    public function appendResponse(ResponseInterface $xResponse, bool $bBefore = false);

    /**
     * Return the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return string
     */
    public function getOutput(): string;

    /**
     * Add a response command that is generated by a plugin
     *
     * @param ResponsePlugin $xPlugin    The plugin object
     * @param string $sName    The command name
     * @param array|JsonSerializable $aOptions    The command options
     *
     * @return ResponseInterface
     */
    public function addPluginCommand(ResponsePlugin $xPlugin, string $sName,
        array|JsonSerializable $aOptions): ResponseInterface;

    /**
     * Convert this response to a PSR7 response object
     *
     * @return PsrResponseInterface
     */
    public function toPsr(): PsrResponseInterface;

    /**
     * Add a command to assign the specified value to the given element's attribute
     *
     * @param string $sTarget    The id of the html element on the browser
     * @param string $sAttribute    The attribute to be assigned
     * @param string $sValue    The value to be assigned to the attribute
     *
     * @return ResponseInterface
     */
    public function assign(string $sTarget, string $sAttribute, string $sValue): ResponseInterface;

    /**
     * Add a command to assign the specified HTML content to the given element
     *
     * This is a shortcut for assign() on the innerHTML attribute.
     *
     * @param string $sTarget    The id of the html element on the browser
     * @param string $sValue    The value to be assigned to the attribute
     *
     * @return ResponseInterface
     */
    public function html(string $sTarget, string $sValue): ResponseInterface;

    /**
     * Add a command to append the specified data to the given element's attribute
     *
     * @param string $sTarget    The id of the element to be updated
     * @param string $sAttribute    The name of the attribute to be appended to
     * @param string $sValue    The data to be appended to the attribute
     *
     * @return ResponseInterface
     */
    public function append(string $sTarget, string $sAttribute, string $sValue): ResponseInterface;

    /**
     * Add a command to prepend the specified data to the given element's attribute
     *
     * @param string $sTarget    The id of the element to be updated
     * @param string $sAttribute    The name of the attribute to be prepended to
     * @param string $sValue    The value to be prepended to the attribute
     *
     * @return ResponseInterface
     */
    public function prepend(string $sTarget, string $sAttribute, string $sValue): ResponseInterface;

    /**
     * Add a command to replace a specified value with another value within the given element's attribute
     *
     * @param string $sTarget    The id of the element to update
     * @param string $sAttribute    The attribute to be updated
     * @param string $sSearch    The needle to search for
     * @param string $sReplace    The data to use in place of the needle
     *
     * @return ResponseInterface
     */
    public function replace(string $sTarget, string $sAttribute,
        string $sSearch, string $sReplace): ResponseInterface;

    /**
     * Add a command to clear the specified attribute of the given element
     *
     * @param string $sTarget    The id of the element to be updated.
     * @param string $sAttribute    The attribute to be cleared
     *
     * @return ResponseInterface
     */
    public function clear(string $sTarget, string $sAttribute = 'innerHTML'): ResponseInterface;

    /**
     * Add a command to remove an element from the document
     *
     * @param string $sTarget    The id of the element to be removed
     *
     * @return ResponseInterface
     */
    public function remove(string $sTarget): ResponseInterface;

    /**
     * Add a command to create a new element on the browser
     *
     * @param string $sParent    The id of the parent element
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return ResponseInterface
     */
    public function create(string $sParent, string $sTag, string $sId): ResponseInterface;

    /**
     * Add a command to insert a new element just prior to the specified element
     *
     * @param string $sBefore    The id of the element used as a reference point for the insertion
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return ResponseInterface
     */
    public function insertBefore(string $sBefore, string $sTag, string $sId): ResponseInterface;

    /**
     * Add a command to insert a new element just prior to the specified element
     * This is an alias for insertBefore.
     *
     * @param string $sBefore    The id of the element used as a reference point for the insertion
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return ResponseInterface
     */
    public function insert(string $sBefore, string $sTag, string $sId): ResponseInterface;

    /**
     * Add a command to insert a new element after the specified
     *
     * @param string $sAfter    The id of the element used as a reference point for the insertion
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return ResponseInterface
     */
    public function insertAfter(string $sAfter, string $sTag, string $sId): ResponseInterface;

    /**
     * Add a command to call the specified javascript function with the given (optional) parameters
     *
     * @param string $sFunc    The name of the function to call
     *
     * @return ResponseInterface
     */
    public function call(string $sFunc): ResponseInterface;

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
    public function addHandler(string $sTarget, string $sEvent, string $sHandler): ResponseInterface;

    /**
     * Add a command to remove an event handler from an element
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param string $sHandler    The name of the javascript function called when the event is fired
     *
     * @return ResponseInterface
     */
    public function removeHandler(string $sTarget, string $sEvent, string $sHandler): ResponseInterface;

    /**
     * Add a command to add an event handler on the specified element
     * This handler can take custom parameters, and is is executed in a specific context.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param JsCall $xCall    The event handler
     *
     * @return ResponseInterface
     */
    public function addEventHandler(string $sTarget, string $sEvent, JsCall $xCall): ResponseInterface;

    /**
     * Add a command to set an event handler on the specified element
     * This handler can take custom parameters, and is is executed in a specific context.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param JsCall $xCall    The event handler
     *
     * @return ResponseInterface
     */
    public function setEventHandler(string $sTarget, string $sEvent, JsCall $xCall): ResponseInterface;

    /**
     * Add a command to set a click handler on the browser
     *
     * @param string $sTarget    The id of the element
     * @param JsCall $xCall    The event handler
     *
     * @return ResponseInterface
     */
    public function onClick(string $sTarget, JsCall $xCall): ResponseInterface;

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
    public function sleep(int $tenths): ResponseInterface;

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
    public function confirmCommands(int $nCommandCount, string $sMessage): ResponseInterface;

    /**
     * Add a command to display an alert message to the user
     *
     * @param string $sMessage    The message to be displayed
     *
     * @return ResponseInterface
     */
    public function alert(string $sMessage): ResponseInterface;

    /**
     * Add a command to ask the browser to navigate to the specified URL
     *
     * @param string $sURL    The relative or fully qualified URL
     * @param integer $nDelay    Number of seconds to delay before the redirect occurs
     *
     * @return ResponseInterface
     */
    public function redirect(string $sURL, int $nDelay = 0): ResponseInterface;

    /**
     * Add a command to display a debug message to the user
     *
     * @param string $sMessage    The message to be displayed
     *
     * @return ResponseInterface
     */
    public function debug(string $sMessage): ResponseInterface;
}
