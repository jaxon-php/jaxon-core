<?php

/**
 * Response.php - The Jaxon Response
 *
 * This class collects commands to be sent back to the browser in response to a jaxon request.
 * Commands are encoded and packaged in json format.
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

use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\Response\DataBag\DataBagContext;
use Jaxon\Plugin\Response\JQuery\DomSelector;
use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Utils\Translation\Translator;

use function array_filter;
use function array_map;
use function is_array;
use function is_integer;
use function json_encode;
use function trim;

class Response implements ResponseInterface
{
    use Traits\CommandTrait;
    use Traits\DomTrait;
    use Traits\JsTrait;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * @var PluginManager
     */
    protected $xPluginManager;

    /**
     * The parameter reader
     *
     * @var ParameterReader
     */
    protected $xParameterReader;

    /**
     * The constructor
     *
     * @param Translator $xTranslator
     * @param PluginManager $xPluginManager
     * @param ParameterReader $xParameterReader
     */
    public function __construct(Translator $xTranslator, PluginManager $xPluginManager, ParameterReader $xParameterReader)
    {
        $this->xTranslator = $xTranslator;
        $this->xPluginManager = $xPluginManager;
        $this->xParameterReader = $xParameterReader;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return 'application/json';
    }

    /**
     * @inheritDoc
     */
    public function getOutput(): string
    {
        return json_encode(['jxnobj' => $this->aCommands]);
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
     * Used to permit plugins to be called as if they were native members of the Response instance.
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
     * Create a JQuery DomSelector, and link it to the current response.
     *
     * This is a shortcut to the JQuery plugin.
     *
     * @param string $sPath    The jQuery selector path
     * @param string $sContext    A context associated to the selector
     *
     * @return DomSelector
     */
    public function jq(string $sPath = '', string $sContext = ''): DomSelector
    {
        return $this->plugin('jquery')->selector($sPath, $sContext);
    }

    /**
     * Get the databag with a given name
     *
     * @param string $sName
     *
     * @return DataBagContext
     */
    public function bag(string $sName): DataBagContext
    {
        return $this->plugin('bags')->bag($sName);;
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
            $aAttributes = array_filter($aAttributes, function($xValue) {
                return $xValue === '';
            });
        }
        $aAttributes['cmd'] = $sName;
        return $this->addCommand($aAttributes, $mData);
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
}
