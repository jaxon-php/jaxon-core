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
use Jaxon\Response\Response;
use Jaxon\Exception\RequestException;

use function call_user_func;
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
     * @return Response
     */
    abstract public function addCommand(array $aAttributes, $mData): Response;

    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param string $sName    The command name
     * @param array $aAttributes    Associative array of attributes that will describe the command
     * @param mixed $mData    The data to be associated with this command
     * @param bool $bRemoveEmpty    If true, remove empty attributes
     *
     * @return Response
     */
    abstract protected function _addCommand(string $sName, array $aAttributes, $mData, bool $bRemoveEmpty = false): Response;

    /**
     * Response command that prompts user with [ok] [cancel] style message box
     *
     * If the user clicks cancel, the specified number of response commands
     * following this one, will be skipped.
     *
     * @param integer $nCommandCount    The number of commands to skip upon cancel
     * @param string $sMessage    The message to display to the user
     *
     * @return Response
     */
    public function confirmCommands(int $nCommandCount, string $sMessage): Response
    {
        $aAttributes = ['count' => $nCommandCount];
        return $this->_addCommand('cc', $aAttributes, $sMessage);
    }

    /**
     * Add a command to display an alert message to the user
     *
     * @param string $sMessage    The message to be displayed
     *
     * @return Response
     */
    public function alert(string $sMessage): Response
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
     * @return Response
     */
    public function redirect(string $sURL, int $nDelay = 0): Response
    {
        $sURL = $this->xPluginManager->getParameterReader()->parseUrl($sURL);
        if($nDelay <= 0)
        {
            return $this->script("window.location = '$sURL';");
        }
        $nDelay *= 1000;
        return $this->script("window.setTimeout(function() { window.location = '$sURL'; }, $nDelay);");
    }

    /**
     * Add a command to execute a portion of javascript on the browser
     *
     * The script runs in its own context, so variables declared locally, using the 'var' keyword,
     * will no longer be available after the call.
     * To construct a variable that will be accessible globally, even after the script has executed,
     * leave off the 'var' keyword.
     *
     * @param string $sJS    The script to execute
     *
     * @return Response
     */
    public function script(string $sJS): Response
    {
        return $this->_addCommand('js', [], $sJS);
    }

    /**
     * Add a command to call the specified javascript function with the given (optional) parameters
     *
     * @param string $sFunc    The name of the function to call
     *
     * @return Response
     */
    public function call(string $sFunc): Response
    {
        $aArgs = func_get_args();
        array_shift($aArgs);
        $aAttributes = ['cmd' => 'jc', 'func' => $sFunc];
        return $this->addCommand($aAttributes, $aArgs);
    }

    /**
     * Add a command to set an event handler on the browser
     *
     * @param string $sTarget    The id of the element that contains the event
     * @param string $sEvent    The name of the event
     * @param string $sScript    The javascript to execute when the event is fired
     *
     * @return Response
     */
    public function setEvent(string $sTarget, string $sEvent, string $sScript): Response
    {
        $aAttributes = ['id' => $sTarget, 'prop' => $sEvent];
        return $this->_addCommand('ev', $aAttributes, $sScript);
    }

    /**
     * Add a command to set a click handler on the browser
     *
     * @param string $sTarget    The id of the element that contains the event
     * @param string $sScript    The javascript to execute when the event is fired
     *
     * @return Response
     */
    public function onClick(string $sTarget, string $sScript): Response
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
     * @return Response
     */
    public function addHandler(string $sTarget, string $sEvent, string $sHandler): Response
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
     * @return Response
     */
    public function removeHandler(string $sTarget, string $sEvent, string $sHandler): Response
    {
        $aAttributes = ['id' => $sTarget, 'prop' => $sEvent];
        return $this->_addCommand('rh', $aAttributes, $sHandler);
    }

    /**
     * Add a command to construct a javascript function on the browser
     *
     * @param string $sFunction    The name of the function to construct
     * @param string $sArgs    Comma separated list of parameter names
     * @param string $sScript    The javascript code that will become the body of the function
     *
     * @return Response
     */
    public function setFunction(string $sFunction, string $sArgs, string $sScript): Response
    {
        $aAttributes = ['func' => $sFunction, 'prop' => $sArgs];
        return $this->_addCommand('sf', $aAttributes, $sScript);
    }

    /**
     * Add a command to construct a wrapper function around an existing javascript function on the browser
     *
     * @param string $sFunction    The name of the existing function to wrap
     * @param string $sArgs    The comma separated list of parameters for the function
     * @param array $aScripts    An array of javascript code snippets that will be used to build
     *                                             the body of the function
     *                                             The first piece of code specified in the array will occur before
     *                                             the call to the original function, the second will occur after
     *                                             the original function is called.
     * @param string $sReturnValueVar    The name of the variable that will retain the return value
     *                                             from the call to the original function
     *
     * @return Response
     */
    public function wrapFunction(string $sFunction, string $sArgs, array $aScripts, string $sReturnValueVar): Response
    {
        $aAttributes = ['cmd' => 'wpf', 'func' => $sFunction, 'prop' => $sArgs, 'type' => $sReturnValueVar];
        return $this->addCommand($aAttributes, $aScripts);
    }

    /**
     * Add a command to load a javascript file on the browser
     *
     * @param bool $bIncludeOnce    Include once or not
     * @param string $sFileName    The relative or fully qualified URI of the javascript file
     * @param string $sType    Determines the script type. Defaults to 'text/javascript'
     * @param string $sId    The wrapper id
     *
     * @return Response
     */
    private function _includeScript(bool $bIncludeOnce, string $sFileName, string $sType, string $sId): Response
    {
        $aAttributes = ['type' => $sType, 'elm_id' => $sId];
        return $this->_addCommand(($bIncludeOnce ? 'ino' : 'in'), $aAttributes, $sFileName, true);
    }

    /**
     * Add a command to load a javascript file on the browser
     *
     * @param string $sFileName    The relative or fully qualified URI of the javascript file
     * @param string $sType    Determines the script type. Defaults to 'text/javascript'
     * @param string $sId    The wrapper id
     *
     * @return Response
     */
    public function includeScript(string $sFileName, string $sType = '', string $sId = ''): Response
    {
        return $this->_includeScript(false, $sFileName, $sType, $sId);
    }

    /**
     * Add a command to include a javascript file on the browser if it has not already been loaded
     *
     * @param string $sFileName    The relative or fully qualified URI of the javascript file
     * @param string $sType    Determines the script type. Defaults to 'text/javascript'
     * @param string $sId    The wrapper id
     *
     * @return Response
     */
    public function includeScriptOnce(string $sFileName, string $sType = '', string $sId = ''): Response
    {
        return $this->_includeScript(true, $sFileName, $sType, $sId);
    }

    /**
     * Add a command to remove a SCRIPT reference to a javascript file on the browser
     *
     * Optionally, you can call a javascript function just prior to the file being unloaded (for cleanup).
     *
     * @param string $sFileName    The relative or fully qualified URI of the javascript file
     * @param string $sUnload    Name of a javascript function to call prior to unlaoding the file
     *
     * @return Response
     */
    public function removeScript(string $sFileName, string $sUnload = ''): Response
    {
        $aAttributes = ['unld' => $sUnload];
        return $this->_addCommand('rjs', $aAttributes, $sFileName, true);
    }

    /**
     * Add a command to include a LINK reference to the specified CSS file on the browser.
     *
     * This will cause the browser to load and apply the style sheet.
     *
     * @param string $sFileName    The relative or fully qualified URI of the css file
     * @param string $sMedia    The media type of the CSS file. Defaults to 'screen'
     *
     * @return Response
     */
    public function includeCSS(string $sFileName, string $sMedia = ''): Response
    {
        $aAttributes = ['media' => $sMedia];
        return $this->_addCommand('css', $aAttributes, $sFileName, true);
    }

    /**
     * Add a command to remove a LINK reference to a CSS file on the browser
     *
     * This causes the browser to unload the style sheet, effectively removing the style changes it caused.
     *
     * @param string $sFileName The relative or fully qualified URI of the css file
     * @param string $sMedia
     *
     * @return Response
     */
    public function removeCSS(string $sFileName, string $sMedia = ''): Response
    {
        $aAttributes = ['media' => $sMedia];
        return $this->_addCommand('rcss', $aAttributes, $sFileName, true);
    }

    /**
     * Add a command to make Jaxon pause while the CSS files are loaded
     *
     * The browser is not typically a multi-threading application, with regards to javascript code.
     * Therefore, the CSS files included or removed with <Response->includeCSS> and
     * <Response->removeCSS> respectively, will not be loaded or removed until the browser regains
     * control from the script.
     * This command returns control back to the browser and pauses the execution of the response
     * until the CSS files, included previously, are loaded.
     *
     * @param integer $nTimeout    The number of 1/10ths of a second to pause before timing out
     *                                             and continuing with the execution of the response commands
     *
     * @return Response
     */
    public function waitForCSS(int $nTimeout = 600): Response
    {
        $aAttributes = ['cmd' => 'wcss', 'prop' => $nTimeout];
        return $this->addCommand($aAttributes, '');
    }

    /**
     * Add a command to make Jaxon to delay execution of the response commands until a specified condition is met
     *
     * Note, this returns control to the browser, so that other script operations can execute.
     * Jaxon will continue to monitor the specified condition and, when it evaluates to true,
     * will continue processing response commands.
     *
     * @param string $script    A piece of javascript code that evaulates to true or false
     * @param integer $tenths    The number of 1/10ths of a second to wait before timing out
     *                                             and continuing with the execution of the response commands.
     *
     * @return Response
     */
    public function waitFor(string $script, int $tenths): Response
    {
        $aAttributes = ['cmd' => 'wf', 'prop' => $tenths];
        return $this->addCommand($aAttributes, $script);
    }

    /**
     * Add a command to make Jaxon to pause execution of the response commands,
     * returning control to the browser so it can perform other commands asynchronously.
     *
     * After the specified delay, Jaxon will continue execution of the response commands.
     *
     * @param integer $tenths    The number of 1/10ths of a second to sleep
     *
     * @return Response
     */
    public function sleep(int $tenths): Response
    {
        $aAttributes = ['cmd' =>'s', 'prop' => $tenths];
        return $this->addCommand($aAttributes, '');
    }
}
