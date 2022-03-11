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

use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Plugin\Response as ResponsePlugin;
use Jaxon\Response\Plugin\JQuery\Dom\Element;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Translation\Translator;

use function json_encode;
use function array_merge;
use function array_map;
use function array_pop;
use function is_integer;
use function trim;
use function is_array;
use function array_keys;
use function count;

class Response extends AbstractResponse
{
    use Features\DomCommands;
    use Features\JsCommands;
    use Features\DomTreeCommands;

    /**
     * @var Config
     */
    protected $xConfig;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * @var PluginManager
     */
    protected $xPluginManager;

    /**
     * The constructor
     */
    public function __construct(Config $xConfig, Translator $xTranslator, PluginManager $xPluginManager)
    {
        $this->xConfig = $xConfig;
        $this->xTranslator = $xTranslator;
        $this->xPluginManager = $xPluginManager;
    }

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
    protected $xReturnValue;

    /**
     * Get the content type, which is always set to 'application/json'
     *
     * @return string
     */
    public function getContentType(): string
    {
        return 'application/json';
    }

    /**
     * Provides access to registered response plugins
     *
     * Pass the plugin name as the first argument and the plugin object will be returned.
     *
     * @param string $sName    The name of the plugin
     *
     * @return null|ResponsePlugin
     */
    public function plugin(string $sName): ?ResponsePlugin
    {
        return $this->xPluginManager->getResponsePlugin($sName, $this);
    }

    /**
     * Magic PHP function
     *
     * Used to permit plugins to be called as if they where native members of the Response instance.
     *
     * @param string $sPluginName    The name of the plugin
     *
     * @return null|ResponsePlugin
     */
    public function __get(string $sPluginName)
    {
        return $this->plugin($sPluginName);
    }

    /**
     * Create a JQuery Element with a given selector, and link it to the current response.
     *
     * This is a shortcut to the JQuery plugin.
     *
     * @param string $sSelector    The jQuery selector
     * @param string $sContext    A context associated to the selector
     *
     * @return Element
     */
    public function jq(string $sSelector = '', string $sContext = ''): Element
    {
        return $this->plugin('jquery')->element($sSelector, $sContext);
    }

    /**
     * Create a JQuery Element with a given selector, and link it to the current response.
     *
     * This is a shortcut to the JQuery plugin.
     *
     * @param string $sSelector    The jQuery selector
     * @param string $sContext    A context associated to the selector
     *
     * @return Element
     */
    public function jQuery(string $sSelector = '', string $sContext = ''): Element
    {
        return $this->jq($sSelector, $sContext);
    }

    /**
     * Get the command data, merged with the last one if possible.
     *
     * @param array $aAttributes    Associative array of attributes that will describe the command
     * @param mixed $mData    The data to be associated with this command
     *
     * @return array
     */
    private function getCommandData(array $aAttributes, $mData): array
    {
        if(!$this->xConfig->getOption('core.response.merge') ||
            !in_array($aAttributes['cmd'], ['js', 'ap']) || ($count = count($this->aCommands)) === 0)
        {
            return [false, $mData];
        }
        $aLastCommand = $this->aCommands[$count - 1];
        if($aLastCommand['cmd'] !== $aAttributes['cmd'])
        {
            return [false, $mData];
        }
        if($aLastCommand['cmd'] === 'js')
        {
            if($this->xConfig->getOption('core.response.merge.js'))
            {
                return [true, $aLastCommand['data'] . '; ' . $mData];
            }
        }
        elseif($aLastCommand['cmd'] === 'ap')
        {
            if($this->xConfig->getOption('core.response.merge.ap') &&
                $aLastCommand['id'] === $aAttributes['id'] &&
                $aLastCommand['prop'] === $aAttributes['prop'])
            {
                return [true, $aLastCommand['data'] . ' ' . $mData];
            }
        }
        return [false, $mData];
    }

    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param array $aAttributes    Associative array of attributes that will describe the command
     * @param mixed $mData    The data to be associated with this command
     *
     * @return Response
     */
    public function addCommand(array $aAttributes, $mData): Response
    {
        $aAttributes = array_map(function($xAttribute) {
            return is_integer($xAttribute) ? $xAttribute : trim((string)$xAttribute, " \t");
        }, $aAttributes);

        [$bMerged, $mData] = $this->getCommandData($aAttributes, $mData);
        if($bMerged)
        {
            array_pop($this->aCommands);
        }
        $aAttributes['data'] = $mData;
        $this->aCommands[] = $aAttributes;

        return $this;
    }

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
    protected function _addCommand(string $sName, array $aAttributes, $mData, bool $bRemoveEmpty = false): Response
    {
        $mData = is_array($mData) ? array_map(function($sData) {
            return trim((string)$sData, " \t\n");
        }, $mData) : trim((string)$mData, " \t\n");

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
    public function clearCommands(): Response
    {
        $this->aCommands = [];
        return $this;
    }

    /**
     * Add a response command that is generated by a plugin
     *
     * @param ResponsePlugin $xPlugin    The plugin object
     * @param array $aAttributes    The attributes for this response command
     * @param mixed $mData    The data to be sent with this command
     *
     * @return Response
     */
    public function addPluginCommand(ResponsePlugin $xPlugin, array $aAttributes, $mData): Response
    {
        $aAttributes['plg'] = $xPlugin->getName();
        return $this->addCommand($aAttributes, $mData);
    }

    /**
     * Merge the response commands from the specified <Response> object with
     * the response commands in this <Response> object
     *
     * @param Response|array $mCommands    The <Response> object
     * @param bool $bBefore    Add the new commands to the beginning of the list
     *
     * @return void
     * @throws SetupException
     */
    public function appendResponse($mCommands, bool $bBefore = false)
    {
        if($mCommands instanceof Response)
        {
            $this->xReturnValue = $mCommands->xReturnValue;
            $aCommands = $mCommands->aCommands;
        }
        elseif(is_array($mCommands))
        {
            $aCommands = $mCommands;
        }
        else
        {
            throw new SetupException($this->xTranslator->trans('errors.response.data.invalid'));
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
    public function getCommands(): array
    {
        return $this->aCommands;
    }

    /**
     * Get the number of commands in the response
     *
     * @return int
     */
    public function getCommandCount(): int
    {
        return count($this->aCommands);
    }

    /**
     * Stores a value that will be passed back as part of the response
     *
     * When making synchronous requests, the calling javascript can obtain this value
     * immediately as the return value of the <jaxon.call> javascript function
     *
     * @param mixed $value    Any value
     *
     * @return Response
     */
    public function setReturnValue($value): Response
    {
        $this->xReturnValue = $value;
        return $this;
    }

    /**
     * Return the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return string
     */
    public function getOutput(): string
    {
        $aResponse = [
            'jxnobj' => [],
        ];
        if(($this->xReturnValue))
        {
            $aResponse['jxnrv'] = $this->xReturnValue;
        }
        foreach($this->aCommands as $xCommand)
        {
            $aResponse['jxnobj'][] = $xCommand;
        }

        return json_encode($aResponse);
    }
}
