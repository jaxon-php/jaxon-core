<?php

/**
 * Response.php - The Jaxon Response
 *
 * This class collects commands to be sent back to the browser in response to a jaxon request.
 * Commands are encoded and packaged in a format that is acceptable to the response handler
 * from the javascript library running on the client side.
 *
 * Common commands include:
 * - <Response->assign>: Assign a value to an element's attribute.
 * - <Response->append>: Append a value on to an element's attribute.
 * - <Response->script>: Execute a portion of javascript code.
 * - <Response->call>: Execute an existing javascript function.
 * - <Response->alert>: Display an alert dialog to the user.
 *
 * Elements are identified by the value of the HTML id attribute.
 * If you do not see your updates occuring on the browser side, ensure that you are using
 * the correct id in your response.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response;

use Jaxon\Jaxon;

class Response
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * The response type
     *
     * @var string
     */
    public $sContentType = 'application/json';

    /**
     * The commands that will be sent to the browser in the response
     *
     * @var array
     */
    public $aCommands;

    /**
     * A string, array or integer value to be returned to the caller when using 'synchronous' mode requests.
     * See <jaxon->setMode> for details.
     *
     * @var mixed
     */
    private $returnValue;

    public function __construct()
    {
        $this->aCommands = array();
    }

    /**
     * Get the content type, which is always set to 'application/json'
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->sContentType;
    }

    /**
     * Get the configured character encoding
     *
     * @return string
     */
    public function getCharacterEncoding()
    {
        return $this->getOption('core.encoding');
    }

    /**
     * Provides access to registered response plugins
     *
     * Pass the plugin name as the first argument and the plugin object will be returned.
     * You can then access the methods of the plugin directly.
     *
     * @param string        $sName                The name of the plugin
     *
     * @return null|\Jaxon\Plugin\Response
     */
    public function plugin($sName)
    {
        $xPlugin = $this->getPluginManager()->getResponsePlugin($sName);
        if(!$xPlugin)
        {
            return null;
        }
        $xPlugin->setResponse($this);
        return $xPlugin;
    }

    /**
     * Create a JQuery Element with a given selector, and link it to the current response.
     *
     * This is a shortcut to the JQuery plugin.
     *
     * @param string        $sSelector            The jQuery selector
     * @param string        $sContext             A context associated to the selector
     *
     * @return Jaxon\JQuery\Dom\Element
     */
    public function jq($sSelector = '', $sContext = '')
    {
        return $this->plugin('jquery')->element($sSelector, $sContext);
    }

    /**
     * Create a JQuery Element with a given selector, and link it to the current response.
     *
     * This is a shortcut to the JQuery plugin.
     *
     * @param string        $sSelector            The jQuery selector
     * @param string        $sContext             A context associated to the selector
     *
     * @return Jaxon\JQuery\Dom\Element
     */
    public function jQuery($sSelector = '', $sContext = '')
    {
        return $this->jq($sSelector, $sContext);
    }

    /**
     * Magic PHP function
     *
     * Used to permit plugins to be called as if they where native members of the Response instance.
     *
     * @param string        $sPluginName        The name of the plugin
     *
     * @return \Jaxon\Plugin\Response
     */
    public function __get($sPluginName)
    {
        return $this->plugin($sPluginName);
    }

    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param array         $aAttributes        Associative array of attributes that will describe the command
     * @param mixed            $mData                The data to be associated with this command
     *
     * @return \Jaxon\Plugin\Response
     */
    public function addCommand($aAttributes, $mData)
    {
        /* merge commands if possible */
        if(in_array($aAttributes['cmd'], array('js', 'ap')))
        {
            if(($aLastCommand = array_pop($this->aCommands)))
            {
                if($aLastCommand['cmd'] == $aAttributes['cmd'])
                {
                    if($this->getOption('core.response.merge.js') &&
                            $aLastCommand['cmd'] == 'js')
                    {
                        $mData = $aLastCommand['data'].'; '.$mData;
                    }
                    elseif($this->getOption('core.response.merge.ap') &&
                            $aLastCommand['cmd'] == 'ap' &&
                            $aLastCommand['id'] == $aAttributes['id'] &&
                            $aLastCommand['prop'] == $aAttributes['prop'])
                    {
                        $mData = $aLastCommand['data'].' '.$mData;
                    }
                    else
                    {
                        $this->aCommands[] = $aLastCommand;
                    }
                }
                else
                {
                    $this->aCommands[] = $aLastCommand;
                }
            }
        }
        $aAttributes['data'] = $mData;
        $this->aCommands[] = $aAttributes;

        return $this;
    }

    /**
     * Clear all the commands already added to the response
     *
     * @return \Jaxon\Plugin\Response
     */
    public function clearCommands()
    {
        $this->aCommands = [];

        return $this;
    }

    /**
     * Add a response command that is generated by a plugin
     *
     * @param \Jaxon\Plugin\Plugin  $xPlugin            The plugin object
     * @param array                 $aAttributes        The attributes for this response command
     * @param mixed                 $mData              The data to be sent with this command
     *
     * @return \Jaxon\Plugin\Response
     */
    public function addPluginCommand($xPlugin, $aAttributes, $mData)
    {
        $aAttributes['plg'] = $xPlugin->getName();
        return $this->addCommand($aAttributes, $mData);
    }

    /**
     * Merge the response commands from the specified <Response> object with
     * the response commands in this <Response> object
     *
     * @param Response        $mCommands            The <Response> object
     * @param boolean        $bBefore            Add the new commands to the beginning of the list
     *
     * @return void
     */
    public function appendResponse($mCommands, $bBefore = false)
    {
        if($mCommands instanceof Response)
        {
            $this->returnValue = $mCommands->returnValue;

            if($bBefore)
            {
                $this->aCommands = array_merge($mCommands->aCommands, $this->aCommands);
            }
            else
            {
                $this->aCommands = array_merge($this->aCommands, $mCommands->aCommands);
            }
        }
        elseif(is_array($mCommands))
        {
            if($bBefore)
            {
                $this->aCommands = array_merge($mCommands, $this->aCommands);
            }
            else
            {
                $this->aCommands = array_merge($this->aCommands, $mCommands);
            }
        }
        else
        {
            if(!empty($mCommands))
            {
                throw new \Jaxon\Exception\Error($this->trans('errors.response.data.invalid'));
            }
        }
    }

    /**
     * Response command that prompts user with [ok] [cancel] style message box
     *
     * If the user clicks cancel, the specified number of response commands
     * following this one, will be skipped.
     *
     * @param integer        $iCmdNumber            The number of commands to skip upon cancel
     * @param string        $sMessage            The message to display to the user
     *
     * @return \Jaxon\Plugin\Response
     */
    public function confirmCommands($iCmdNumber, $sMessage)
    {
        return $this->addCommand(
            array(
                'cmd' => 'cc',
                'id' => $iCmdNumber
            ),
            trim((string)$sMessage, " \t\n")
        );
    }

    /**
     * Add a command to assign the specified value to the given element's attribute
     *
     * @param string        $sTarget              The id of the html element on the browser
     * @param string        $sAttribute           The attribute to be assigned
     * @param string        $sData                The value to be assigned to the attribute
     *
     * @return \Jaxon\Plugin\Response
     */
    public function assign($sTarget, $sAttribute, $sData)
    {
        return $this->addCommand(
            array(
                'cmd' => 'as',
                'id' => trim((string)$sTarget, " \t"),
                'prop' => trim((string)$sAttribute, " \t")
            ),
            trim((string)$sData, " \t\n")
        );
    }

    /**
     * Add a command to assign the specified HTML content to the given element
     *
     * This is a shortcut for assign() on the innerHTML attribute.
     *
     * @param string        $sTarget              The id of the html element on the browser
     * @param string        $sData                The value to be assigned to the attribute
     *
     * @return \Jaxon\Plugin\Response
     */
    public function html($sTarget, $sData)
    {
        return $this->assign($sTarget, 'innerHTML', $sData);
    }

    /**
     * Add a command to append the specified data to the given element's attribute
     *
     * @param string        $sTarget            The id of the element to be updated
     * @param string        $sAttribute            The name of the attribute to be appended to
     * @param string        $sData                The data to be appended to the attribute
     *
     * @return \Jaxon\Plugin\Response
     */
    public function append($sTarget, $sAttribute, $sData)
    {
        return $this->addCommand(
            array(
                'cmd' => 'ap',
                'id' => trim((string)$sTarget, " \t"),
                'prop' => trim((string)$sAttribute, " \t")
            ),
            trim((string)$sData, " \t\n")
        );
    }

    /**
     * Add a command to prepend the specified data to the given element's attribute
     *
     * @param string        $sTarget            The id of the element to be updated
     * @param string        $sAttribute            The name of the attribute to be prepended to
     * @param string        $sData                The value to be prepended to the attribute
     *
     * @return \Jaxon\Plugin\Response
     */
    public function prepend($sTarget, $sAttribute, $sData)
    {
        return $this->addCommand(
            array(
                'cmd' => 'pp',
                'id' => trim((string)$sTarget, " \t"),
                'prop' => trim((string)$sAttribute, " \t")
            ),
            trim((string)$sData, " \t\n")
        );
    }

    /**
     * Add a command to replace a specified value with another value within the given element's attribute
     *
     * @param string        $sTarget            The id of the element to update
     * @param string        $sAttribute            The attribute to be updated
     * @param string        $sSearch            The needle to search for
     * @param string        $sData                The data to use in place of the needle
     *
     * @return \Jaxon\Plugin\Response
     */
    public function replace($sTarget, $sAttribute, $sSearch, $sData)
    {
        return $this->addCommand(
            array(
                'cmd' => 'rp',
                'id' => trim((string)$sTarget, " \t"),
                'prop' => trim((string)$sAttribute, " \t")
            ),
            array(
                's' => trim((string)$sSearch, " \t\n"),
                'r' => trim((string)$sData, " \t\n")
            )
        );
    }

    /**
     * Add a command to clear the specified attribute of the given element
     *
     * @param string        $sTarget            The id of the element to be updated.
     * @param string        $sAttribute            The attribute to be cleared
     *
     * @return \Jaxon\Plugin\Response
     */
    public function clear($sTarget, $sAttribute)
    {
        return $this->assign(trim((string)$sTarget, " \t"), trim((string)$sAttribute, " \t"), '');
    }

    /**
     * Add a command to assign a value to a member of a javascript object (or element)
     * that is specified by the context member of the request
     *
     * The object is referenced using the 'this' keyword in the sAttribute parameter.
     *
     * @param string        $sAttribute            The attribute to be updated
     * @param string        $sData                The value to assign
     *
     * @return \Jaxon\Plugin\Response
     */
    public function contextAssign($sAttribute, $sData)
    {
        return $this->addCommand(
            array(
                'cmd' => 'c:as',
                'prop' => trim((string)$sAttribute, " \t")
            ),
            trim((string)$sData, " \t\n")
        );
    }

    /**
     * Add a command to append a value onto the specified member of the javascript
     * context object (or element) specified by the context member of the request
     *
     * The object is referenced using the 'this' keyword in the sAttribute parameter.
     *
     * @param string        $sAttribute            The attribute to be appended to
     * @param string        $sData                The value to append
     *
     * @return \Jaxon\Plugin\Response
     */
    public function contextAppend($sAttribute, $sData)
    {
        return $this->addCommand(
            array(
                'cmd' => 'c:ap',
                'prop' => trim((string)$sAttribute, " \t")
            ),
            trim((string)$sData, " \t\n")
        );
    }

    /**
     * Add a command to prepend the speicified data to the given member of the current
     * javascript object specified by context in the current request
     *
     * The object is access via the 'this' keyword in the sAttribute parameter.
     *
     * @param string        $sAttribute            The attribute to be updated
     * @param string        $sData                The value to be prepended
     *
     * @return \Jaxon\Plugin\Response
     */
    public function contextPrepend($sAttribute, $sData)
    {
        return $this->addCommand(
            array(
                'cmd' => 'c:pp',
                'prop' => trim((string)$sAttribute, " \t")
            ),
            trim((string)$sData, " \t\n")
        );
    }

    /**
     * Add a command to to clear the value of the attribute specified in the sAttribute parameter
     *
     * The member is access via the 'this' keyword and can be used to update a javascript
     * object specified by context in the request parameters.
     *
     * @param string        $sAttribute            The attribute to be cleared
     *
     * @return \Jaxon\Plugin\Response
     */
    public function contextClear($sAttribute)
    {
        return $this->contextAssign(trim((string)$sAttribute, " \t"), '');
    }

    /**
     * Add a command to display an alert message to the user
     *
     * @param string        $sMessage            The message to be displayed
     *
     * @return \Jaxon\Plugin\Response
     */
    public function alert($sMessage)
    {
        return $this->addCommand(
            array(
                'cmd' => 'al'
            ),
            trim((string)$sMessage, " \t\n")
        );
    }

    /**
     * Add a command to display a debug message to the user
     *
     * @param string        $sMessage            The message to be displayed
     *
     * @return \Jaxon\Plugin\Response
     */
    public function debug($sMessage)
    {
        return $this->addCommand(
            array(
                'cmd' => 'dbg'
            ),
            trim((string)$sMessage, " \t\n")
        );
    }

    /**
     * Add a command to ask the browser to navigate to the specified URL
     *
     * @param string        $sURL                The relative or fully qualified URL
     * @param integer        $iDelay                Number of seconds to delay before the redirect occurs
     *
     * @return \Jaxon\Plugin\Response
     */
    public function redirect($sURL, $iDelay=0)
    {
        // we need to parse the query part so that the values are rawurlencode()'ed
        // can't just use parse_url() cos we could be dealing with a relative URL which
        // parse_url() can't deal with.
        $queryStart = strpos($sURL, '?', strrpos($sURL, '/'));
        if($queryStart !== false)
        {
            $queryStart++;
            $queryEnd = strpos($sURL, '#', $queryStart);
            if($queryEnd === false)
                $queryEnd = strlen($sURL);
            $queryPart = substr($sURL, $queryStart, $queryEnd-$queryStart);
            parse_str($queryPart, $queryParts);
            $newQueryPart = "";
            if($queryParts)
            {
                $first = true;
                foreach($queryParts as $key => $value)
                {
                    if($first)
                        $first = false;
                    else
                        $newQueryPart .= '&';
                    $newQueryPart .= rawurlencode($key).'='.rawurlencode($value);
                }
            } elseif($_SERVER['QUERY_STRING']) {
                    //couldn't break up the query, but there's one there
                    //possibly "http://url/page.html?query1234" type of query?
                    //just encode it and hope it works
                    $newQueryPart = rawurlencode($_SERVER['QUERY_STRING']);
                }
            $sURL = str_replace($queryPart, $newQueryPart, $sURL);
        }
        if($iDelay)
            $this->script('window.setTimeout("window.location = \'' . $sURL . '\';",' . ($iDelay*1000) . ');');
        else
            $this->script('window.location = "' . $sURL . '";');
        return $this;
    }

    /**
     * Add a command to execute a portion of javascript on the browser
     *
     * The script runs in it's own context, so variables declared locally, using the 'var' keyword,
     * will no longer be available after the call.
     * To construct a variable that will be accessable globally, even after the script has executed,
     * leave off the 'var' keyword.
     *
     * @param string        $sJS                The script to execute
     *
     * @return \Jaxon\Plugin\Response
     */
    public function script($sJS)
    {
        return $this->addCommand(
            array(
                'cmd' => 'js'
            ),
            trim((string)$sJS, " \t\n")
        );
    }

    /**
     * Add a command to call the specified javascript function with the given (optional) parameters
     *
     * @param string        $sFunc                The name of the function to call
     *
     * @return \Jaxon\Plugin\Response
     */
    public function call($sFunc)
    {
        $aArgs = func_get_args();
        $sFunc = array_shift($aArgs);
        return $this->addCommand(
            array(
                'cmd' => 'jc',
                'func' => $sFunc
            ),
            $aArgs
        );
    }

    /**
     * Add a command to remove an element from the document
     *
     * @param string        $sTarget            The id of the element to be removed
     *
     * @return \Jaxon\Plugin\Response
     */
    public function remove($sTarget)
    {
        return $this->addCommand(
            array(
                'cmd' => 'rm',
                'id' => trim((string)$sTarget, " \t")
            ),
            ''
        );
    }

    /**
     * Add a command to create a new element on the browser
     *
     * @param string        $sParent            The id of the parent element
     * @param string        $sTag                The tag name to be used for the new element
     * @param string        $sId                The id to assign to the new element
     *
     * @return \Jaxon\Plugin\Response
     */
    public function create($sParent, $sTag, $sId)
    {
        return $this->addCommand(
            array(
                'cmd' => 'ce',
                'id' => trim((string)$sParent, " \t"),
                'prop' => trim((string)$sId, " \t")
            ),
            trim((string)$sTag, " \t\n")
        );
    }

    /**
     * Add a command to insert a new element just prior to the specified element
     *
     * @param string        $sBefore            The id of the element used as a reference point for the insertion
     * @param string        $sTag               The tag name to be used for the new element
     * @param string        $sId                The id to assign to the new element
     *
     * @return \Jaxon\Plugin\Response
     */
    public function insert($sBefore, $sTag, $sId)
    {
        return $this->addCommand(
            array(
                'cmd' => 'ie',
                'id' => trim((string)$sBefore, " \t"),
                'prop' => trim((string)$sId, " \t")
            ),
            trim((string)$sTag, " \t\n")
        );
    }

    /**
     * Add a command to insert a new element after the specified
     *
     * @param string        $sAfter             The id of the element used as a reference point for the insertion
     * @param string        $sTag               The tag name to be used for the new element
     * @param string        $sId                The id to assign to the new element
     *
     * @return \Jaxon\Plugin\Response
     */
    public function insertAfter($sAfter, $sTag, $sId)
    {
        return $this->addCommand(
            array(
                'cmd' => 'ia',
                'id' => trim((string)$sAfter, " \t"),
                'prop' => trim((string)$sId, " \t")
            ),
            trim((string)$sTag, " \t\n")
        );
    }

    /**
     * Add a command to create an input element on the browser
     *
     * @param string        $sParent            The id of the parent element
     * @param string        $sType                The type of the new input element
     * @param string        $sName                The name of the new input element
     * @param string        $sId                The id of the new element
     *
     * @return \Jaxon\Plugin\Response
     */
    public function createInput($sParent, $sType, $sName, $sId)
    {
        return $this->addCommand(
            array(
                'cmd' => 'ci',
                'id' => trim((string)$sParent, " \t"),
                'prop' => trim((string)$sId, " \t"),
                'type' => trim((string)$sType, " \t")
            ),
            trim((string)$sName, " \t\n")
        );
    }

    /**
     * Add a command to insert a new input element preceding the specified element
     *
     * @param string        $sBefore            The id of the element to be used as the reference point for the insertion
     * @param string        $sType                The type of the new input element
     * @param string        $sName                The name of the new input element
     * @param string        $sId                The id of the new element
     *
     * @return \Jaxon\Plugin\Response
     */
    public function insertInput($sBefore, $sType, $sName, $sId)
    {
        return $this->addCommand(
            array(
                'cmd' => 'ii',
                'id' => trim((string)$sBefore, " \t"),
                'prop' => trim((string)$sId, " \t"),
                'type' => trim((string)$sType, " \t")
            ),
            trim((string)$sName, " \t\n")
        );
    }

    /**
     * Add a command to insert a new input element after the specified element
     *
     * @param string        $sAfter                The id of the element to be used as the reference point for the insertion
     * @param string        $sType                The type of the new input element
     * @param string        $sName                The name of the new input element
     * @param string        $sId                The id of the new element
     *
     * @return \Jaxon\Plugin\Response
     */
    public function insertInputAfter($sAfter, $sType, $sName, $sId)
    {
        return $this->addCommand(
            array(
                'cmd' => 'iia',
                'id' => trim((string)$sAfter, " \t"),
                'prop' => trim((string)$sId, " \t"),
                'type' => trim((string)$sType, " \t")
            ),
            trim((string)$sName, " \t\n")
        );
    }

    /**
     * Add a command to set an event handler on the browser
     *
     * @param string        $sTarget            The id of the element that contains the event
     * @param string        $sEvent                The name of the event
     * @param string        $sScript            The javascript to execute when the event is fired
     *
     * @return \Jaxon\Plugin\Response
     */
    public function setEvent($sTarget, $sEvent, $sScript)
    {
        return $this->addCommand(
            array(
                'cmd' => 'ev',
                'id' => trim((string)$sTarget, " \t"),
                'prop' => trim((string)$sEvent, " \t")
            ),
            trim((string)$sScript, " \t\n")
        );
    }

    /**
     * Add a command to set a click handler on the browser
     *
     * @param string        $sTarget            The id of the element that contains the event
     * @param string        $sScript            The javascript to execute when the event is fired
     *
     * @return \Jaxon\Plugin\Response
     */
    public function onClick($sTarget, $sScript)
    {
        return $this->setEvent($sTarget, 'onclick', $sScript);
    }

    /**
     * Add a command to install an event handler on the specified element
     *
     * You can add more than one event handler to an element's event using this method.
     *
     * @param string        $sTarget             The id of the element
     * @param string        $sEvent              The name of the event
     * @param string        $sHandler            The name of the javascript function to call when the event is fired
     *
     * @return \Jaxon\Plugin\Response
     */
    public function addHandler($sTarget, $sEvent, $sHandler)
    {
        return $this->addCommand(
            array(
                'cmd' => 'ah',
                'id' => trim((string)$sTarget, " \t"),
                'prop' => trim((string)$sEvent, " \t")
            ),
            trim((string)$sHandler, " \t\n")
        );
    }

    /**
     * Add a command to remove an event handler from an element
     *
     * @param string        $sTarget             The id of the element
     * @param string        $sEvent              The name of the event
     * @param string        $sHandler            The name of the javascript function called when the event is fired
     *
     * @return \Jaxon\Plugin\Response
     */
    public function removeHandler($sTarget, $sEvent, $sHandler)
    {
        return $this->addCommand(
            array(
                'cmd' => 'rh',
                'id' => trim((string)$sTarget, " \t"),
                'prop' => trim((string)$sEvent, " \t")
            ),
            trim((string)$sHandler, " \t\n")
        );
    }

    /**
     * Add a command to construct a javascript function on the browser
     *
     * @param string        $sFunction            The name of the function to construct
     * @param string        $sArgs                Comma separated list of parameter names
     * @param string        $sScript            The javascript code that will become the body of the function
     *
     * @return \Jaxon\Plugin\Response
     */
    public function setFunction($sFunction, $sArgs, $sScript)
    {
        return $this->addCommand(
            array(
                'cmd' => 'sf',
                'func' => trim((string)$sFunction, " \t"),
                'prop' => trim((string)$sArgs, " \t")
            ),
            trim((string)$sScript, " \t\n")
        );
    }

    /**
     * Add a command to construct a wrapper function around an existing javascript function on the browser
     *
     * @param string        $sFunction            The name of the existing function to wrap
     * @param string        $sArgs                The comma separated list of parameters for the function
     * @param array            $aScripts            An array of javascript code snippets that will be used to build
     *                                             the body of the function
     *                                             The first piece of code specified in the array will occur before
     *                                             the call to the original function, the second will occur after
     *                                             the original function is called.
     * @param string        $sReturnValueVar    The name of the variable that will retain the return value
     *                                             from the call to the original function
     *
     * @return \Jaxon\Plugin\Response
     */
    public function wrapFunction($sFunction, $sArgs, $aScripts, $sReturnValueVar)
    {
        return $this->addCommand(
            array(
                'cmd' => 'wpf',
                'func' => trim((string)$sFunction, " \t"),
                'prop' => trim((string)$sArgs, " \t"),
                'type' => trim((string)$sReturnValueVar, " \t")
            ),
            $aScripts
        );
    }

    /**
     * Add a command to load a javascript file on the browser
     *
     * @param string        $sFileName            The relative or fully qualified URI of the javascript file
     * @param string        $sType                Determines the script type. Defaults to 'text/javascript'
     *
     * @return \Jaxon\Plugin\Response
     */
    public function includeScript($sFileName, $sType = null, $sId = null)
    {
        $command = array('cmd'  =>  'in');

        if(($sType))
            $command['type'] = trim((string)$sType, " \t");

        if(($sId))
            $command['elm_id'] = trim((string)$sId, " \t");

        return $this->addCommand($command, trim((string)$sFileName, " \t"));
    }

    /**
     * Add a command to include a javascript file on the browser if it has not already been loaded
     *
     * @param string        $sFileName            The relative or fully qualified URI of the javascript file
     * @param string        $sType                Determines the script type. Defaults to 'text/javascript'
     *
     * @return \Jaxon\Plugin\Response
     */
    public function includeScriptOnce($sFileName, $sType = null, $sId = null)
    {
        $command = array('cmd' => 'ino');

        if(($sType))
            $command['type'] = trim((string)$sType, " \t");

        if(($sId))
            $command['elm_id'] = trim((string)$sId, " \t");

        return $this->addCommand($command, trim((string)$sFileName, " \t"));
    }

    /**
     * Add a command to remove a SCRIPT reference to a javascript file on the browser
     *
     * Optionally, you can call a javascript function just prior to the file being unloaded (for cleanup).
     *
     * @param string        $sFileName            The relative or fully qualified URI of the javascript file
     * @param string        $sUnload            Name of a javascript function to call prior to unlaoding the file
     *
     * @return \Jaxon\Plugin\Response
     */
    public function removeScript($sFileName, $sUnload = '')
    {
        return $this->addCommand(
            array(
                'cmd' => 'rjs',
                'unld' => trim((string)$sUnload, " \t")
            ),
            trim((string)$sFileName, " \t")
        );
    }

    /**
     * Add a command to include a LINK reference to the specified CSS file on the browser.
     *
     * This will cause the browser to load and apply the style sheet.
     *
     * @param string        $sFileName            The relative or fully qualified URI of the css file
     * @param string        $sMedia                The media type of the CSS file. Defaults to 'screen'
     *
     * @return \Jaxon\Plugin\Response
     */
    public function includeCSS($sFileName, $sMedia = null)
    {
        $command = array('cmd' => 'css');

        if(($sMedia))
            $command['media'] = trim((string)$sMedia, " \t");

        return $this->addCommand($command, trim((string)$sFileName, " \t"));
    }

    /**
     * Add a command to remove a LINK reference to a CSS file on the browser
     *
     * This causes the browser to unload the style sheet, effectively removing the style changes it caused.
     *
     * @param string        $sFileName            The relative or fully qualified URI of the css file
     *
     * @return \Jaxon\Plugin\Response
     */
    public function removeCSS($sFileName, $sMedia = null)
    {
        $command = array('cmd' => 'rcss');

        if(($sMedia))
            $command['media'] = trim((string)$sMedia, " \t");

        return $this->addCommand($command, trim((string)$sFileName, " \t"));
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
     * @param integer        $iTimeout            The number of 1/10ths of a second to pause before timing out
     *                                             and continuing with the execution of the response commands
     *
     * @return \Jaxon\Plugin\Response
     */
    public function waitForCSS($iTimeout = 600)
    {
        $sData = "";
        return $this->addCommand(
            array(
                'cmd' => 'wcss',
                'prop' => $iTimeout
            ),
            $sData
        );
    }

    /**
     * Add a command to make Jaxon to delay execution of the response commands until a specified condition is met
     *
     * Note, this returns control to the browser, so that other script operations can execute.
     * Jaxon will continue to monitor the specified condition and, when it evaulates to true,
     * will continue processing response commands.
     *
     * @param string        $script                A piece of javascript code that evaulates to true or false
     * @param integer        $tenths                The number of 1/10ths of a second to wait before timing out
     *                                             and continuing with the execution of the response commands.
     *
     * @return \Jaxon\Plugin\Response
     */
    public function waitFor($script, $tenths)
    {
        return $this->addCommand(
            array(
                'cmd' => 'wf',
                'prop' => $tenths
            ),
            trim((string)$script, " \t\n")
        );
    }

    /**
     * Add a command to make Jaxon to pause execution of the response commands,
     * returning control to the browser so it can perform other commands asynchronously.
     *
     * After the specified delay, Jaxon will continue execution of the response commands.
     *
     * @param integer        $tenths                The number of 1/10ths of a second to sleep
     *
     * @return \Jaxon\Plugin\Response
     */
    public function sleep($tenths)
    {
        return $this->addCommand(
            array(
                'cmd' => 's',
                'prop' => $tenths
            ),
            ''
        );
    }

    /**
     * Add a command to start a DOM response
     *
     * @return \Jaxon\Plugin\Response
     */
    public function domStartResponse()
    {
        $this->script('jxnElm = []');
    }

    /**
     * Add a command to create a DOM element
     *
     * @param string        $variable            The DOM element name (id or class)
     * @param string        $tag                The HTML tag of the new DOM element
     *
     * @return \Jaxon\Plugin\Response
     */
    public function domCreateElement($variable, $tag)
    {
        return $this->addCommand(
            array(
                'cmd' => 'DCE',
                'tgt' => $variable
            ),
            $tag
        );
    }

    /**
     * Add a command to set an attribute on a DOM element
     *
     * @param string        $variable            The DOM element name (id or class)
     * @param string        $key                The name of the attribute
     * @param string        $value                The value of the attribute
     *
     * @return \Jaxon\Plugin\Response
     */
    public function domSetAttribute($variable, $key, $value)
    {
        return $this->addCommand(
            array(
                'cmd' => 'DSA',
                'tgt' => $variable,
                'key' => $key
            ),
            $value
        );
    }

    /**
     * Add a command to remove children from a DOM element
     *
     * @param string        $parent                The DOM parent element
     * @param string        $skip                The ??
     * @param string        $remove                The ??
     *
     * @return \Jaxon\Plugin\Response
     */
    public function domRemoveChildren($parent, $skip = null, $remove = null)
    {
        $command = array('cmd' => 'DRC');

        if(($skip))
            $command['skip'] = $skip;

        if(($remove))
            $command['remove'] = $remove;

        return $this->addCommand($command, $parent);
    }

    /**
     * Add a command to append a child to a DOM element
     *
     * @param string        $parent                The DOM parent element
     * @param string        $variable            The DOM element name (id or class)
     *
     * @return \Jaxon\Plugin\Response
     */
    public function domAppendChild($parent, $variable)
    {
        return $this->addCommand(
            array(
                'cmd' => 'DAC',
                'par' => $parent
            ),
            $variable
        );
    }

    /**
     * Add a command to insert a DOM element before another
     *
     * @param string        $target                The DOM target element
     * @param string        $variable            The DOM element name (id or class)
     *
     * @return \Jaxon\Plugin\Response
     */
    public function domInsertBefore($target, $variable)
    {
        return $this->addCommand(
            array(
                'cmd' => 'DIB',
                'tgt' => $target
            ),
            $variable
        );
    }

    /**
     * Add a command to insert a DOM element after another
     *
     * @param string        $target                The DOM target element
     * @param string        $variable            The DOM element name (id or class)
     *
     * @return \Jaxon\Plugin\Response
     */
    public function domInsertAfter($target, $variable)
    {
        return $this->addCommand(
            array(
                'cmd' => 'DIA',
                'tgt' => $target
            ),
            $variable
        );
    }

    /**
     * Add a command to append a text to a DOM element
     *
     * @param string        $parent                The DOM parent element
     * @param string        $text                The HTML text to append
     *
     * @return \Jaxon\Plugin\Response
     */
    public function domAppendText($parent, $text)
    {
        return $this->addCommand(
            array(
                'cmd' => 'DAT',
                'par' => $parent
            ),
            $text
        );
    }

    /**
     * Add a command to end a DOM response
     *
     * @return \Jaxon\Plugin\Response
     */
    public function domEndResponse()
    {
        $this->script('jxnElm = []');
    }

    /**
     * Get the number of commands in the response
     *
     * @return integer
     */
    public function getCommandCount()
    {
        return count($this->aCommands);
    }

    /**
     * Stores a value that will be passed back as part of the response
     *
     * When making synchronous requests, the calling javascript can obtain this value
     * immediately as the return value of the <jaxon.call> javascript function
     *
     * @param mixed        $value                Any value
     *
     * @return \Jaxon\Plugin\Response
     */
    public function setReturnValue($value)
    {
        $this->returnValue = $value;
        return $this;
    }

    /**
     * Used internally to generate the response headers
     *
     * @return void
     */
    public function sendHeaders()
    {
        $xRequestManager = $this->getRequestManager();
        if($xRequestManager->getRequestMethod() == Jaxon::METHOD_GET)
        {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
        }

        $sCharacterSet = '';
        $sCharacterEncoding = trim($this->getOption('core.encoding'));
        if(($sCharacterEncoding) && strlen($sCharacterEncoding) > 0)
        {
            $sCharacterSet = '; charset="' . trim($sCharacterEncoding) . '"';
        }

        header('content-type: ' . $this->sContentType . ' ' . $sCharacterSet);
    }

    /**
     * Return the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return string
     */
    public function getOutput()
    {
        $response = array();

        if(($this->returnValue))
        {
            $response['jxnrv'] = $this->returnValue;
        }
        $response['jxnobj'] = array();

        foreach($this->aCommands as $xCommand)
        {
            $response['jxnobj'][] = $xCommand;
        }

        return json_encode($response);
    }

    /**
     * Print the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return void
     */
    public function printOutput()
    {
        print $this->getOutput();
    }
}
