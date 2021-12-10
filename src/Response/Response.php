<?php

/**
 * Response.php - The Jaxon Response
 *
 * This class collects commands to be sent back to the browser in response to a jaxon request.
 * Commands are encoded and packaged in json format.
 *
 * Common commands include:
 * - <Response->assign>: Assign a value to an element's attribute.
 * - <Response->append>: Append a value on to an element's attribute.
 * - <Response->script>: Execute a portion of javascript code.
 * - <Response->call>: Execute an existing javascript function.
 * - <Response->alert>: Display an alert dialog to the user.
 *
 * Elements are identified by the value of the HTML id attribute.
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

use Jaxon\Response\Plugin\JQuery\Dom\Element;

class Response extends AbstractResponse
{
    use Features\DomCommands;
    use Features\JsCommands;
    use Features\DomTreeCommands;

    /**
     * The commands that will be sent to the browser in the response
     *
     * @var array
     */
    protected $aCommands = [];

    /**
     * A string, array or integer value to be returned to the caller when using 'synchronous' mode requests.
     * See <jaxon->setMode> for details.
     *
     * @var mixed
     */
    protected $returnValue;

    /**
     * Get the content type, which is always set to 'application/json'
     *
     * @return string
     */
    public function getContentType()
    {
        return 'application/json';
    }

    /**
     * Provides access to registered response plugins
     *
     * Pass the plugin name as the first argument and the plugin object will be returned.
     *
     * @param string        $sName                The name of the plugin
     *
     * @return null|\Jaxon\Plugin\Response
     */
    public function plugin($sName)
    {
        $xPlugin = jaxon()->di()->getPluginManager()->getResponsePlugin($sName);
        if(!$xPlugin)
        {
            return null;
        }
        $xPlugin->setResponse($this);
        return $xPlugin;
    }

    /**
     * Magic PHP function
     *
     * Used to permit plugins to be called as if they where native members of the Response instance.
     *
     * @param string        $sPluginName        The name of the plugin
     *
     * @return null|\Jaxon\Plugin\Response
     */
    public function __get($sPluginName)
    {
        return $this->plugin($sPluginName);
    }

    /**
     * Create a JQuery Element with a given selector, and link it to the current response.
     *
     * This is a shortcut to the JQuery plugin.
     *
     * @param string        $sSelector            The jQuery selector
     * @param string        $sContext             A context associated to the selector
     *
     * @return Element
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
     * @return Element
     */
    public function jQuery($sSelector = '', $sContext = '')
    {
        return $this->jq($sSelector, $sContext);
    }

    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param array             $aAttributes        Associative array of attributes that will describe the command
     * @param mixed             $mData              The data to be associated with this command
     *
     * @return Response
     */
    public function addCommand(array $aAttributes, $mData)
    {
        array_walk($aAttributes, function(&$sAttribute) {
            if(!is_integer($sAttribute))
            {
                $sAttribute = trim((string)$sAttribute, " \t");
            }
        });

        /* merge commands if possible */
        if(in_array($aAttributes['cmd'], ['js', 'ap']))
        {
            if(($aLastCommand = array_pop($this->aCommands)))
            {
                if($aLastCommand['cmd'] == $aAttributes['cmd'])
                {
                    if($this->getOption('core.response.merge.js') && $aLastCommand['cmd'] == 'js')
                    {
                        $mData = $aLastCommand['data'] . '; ' . $mData;
                    }
                    elseif($this->getOption('core.response.merge.ap') &&
                        $aLastCommand['cmd'] == 'ap' &&
                        $aLastCommand['id'] == $aAttributes['id'] &&
                        $aLastCommand['prop'] == $aAttributes['prop'])
                    {
                        $mData = $aLastCommand['data'] . ' ' . $mData;
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
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param string        $sName              The command name
     * @param array         $aAttributes        Associative array of attributes that will describe the command
     * @param mixed         $mData              The data to be associated with this command
     * @param boolean       $bRemoveEmpty       If true, remove empty attributes
     *
     * @return Response
     */
    protected function _addCommand($sName, array $aAttributes, $mData, $bRemoveEmpty = false)
    {
        if(is_array($mData))
        {
            array_walk($mData, function(&$sData) {
                $sData = trim((string)$sData, " \t\n");
            });
        }
        else
        {
            $mData = trim((string)$mData, " \t\n");
        }

        if($bRemoveEmpty)
        {
            foreach(array_keys($aAttributes) as $sAttr)
            {
                if($aAttributes[$sAttr] === '')
                {
                    unset($aAttributes[$sAttr]);
                }
            }
        }

        $aAttributes['cmd'] = $sName;
        return $this->addCommand($aAttributes, $mData);
    }

    /**
     * Clear all the commands already added to the response
     *
     * @return Response
     */
    public function clearCommands()
    {
        $this->aCommands = [];

        return $this;
    }

    /**
     * Add a response command that is generated by a plugin
     *
     * @param \Jaxon\Plugin\Response    $xPlugin            The plugin object
     * @param array                     $aAttributes        The attributes for this response command
     * @param string                    $mData              The data to be sent with this command
     *
     * @return Response
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
     * @param Response|array    $mCommands          The <Response> object
     * @param boolean           $bBefore            Add the new commands to the beginning of the list
     *
     * @return void
     */
    public function appendResponse($mCommands, $bBefore = false)
    {
        if($mCommands instanceof Response)
        {
            $this->returnValue = $mCommands->returnValue;
            $aCommands = $mCommands->aCommands;
        }
        elseif(is_array($mCommands))
        {
            $aCommands = $mCommands;
        }
        else
        {
            throw new \Jaxon\Exception\Error(jaxon_trans('errors.response.data.invalid'));
        }

        $this->aCommands = ($bBefore) ?
            array_merge($aCommands, $this->aCommands) :
            array_merge($this->aCommands, $aCommands);
    }

    /**
     * Get the commands in the response
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->aCommands;
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
     * @return Response
     */
    public function setReturnValue($value)
    {
        $this->returnValue = $value;
        return $this;
    }

    /**
     * Return the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return string
     */
    public function getOutput()
    {
        $response = [
            'jxnobj' => [],
        ];

        if(($this->returnValue))
        {
            $response['jxnrv'] = $this->returnValue;
        }

        foreach($this->aCommands as $xCommand)
        {
            $response['jxnobj'][] = $xCommand;
        }

        return json_encode($response);
    }
}
