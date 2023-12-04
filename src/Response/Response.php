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
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface as PsrRequestInterface;

use function array_filter;
use function array_map;
use function gmdate;
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
     * @var PluginManager
     */
    protected $xPluginManager;

    /**
     * @var Psr17Factory
     */
    protected $xPsr17Factory;

    /**
     * @var PsrRequestInterface
     */
    protected $xRequest;

    /**
     * The constructor
     *
     * @param PluginManager $xPluginManager
     * @param Psr17Factory $xPsr17Factory
     * @param PsrRequestInterface $xRequest
     */
    public function __construct(PluginManager $xPluginManager, Psr17Factory $xPsr17Factory, PsrRequestInterface $xRequest)
    {
        $this->xPluginManager = $xPluginManager;
        $this->xPsr17Factory = $xPsr17Factory;
        $this->xRequest = $xRequest;
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
        if($this->getCommandCount() === 0)
        {
            return '{}';
        }
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
     * @param mixed $xContext    A context associated to the selector
     *
     * @return DomSelector
     */
    public function jq(string $sPath = '', $xContext = null): DomSelector
    {
        return $this->plugin('jquery')->selector($sPath, $xContext);
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
        return $this->plugin('bags')->bag($sName);
    }

    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param array $aAttributes    Associative array of attributes that will describe the command
     * @param mixed $mData    The data to be associated with this command
     *
     * @return ResponseInterface
     */
    public function addRawCommand(array $aAttributes, $mData): ResponseInterface
    {
        $aAttributes['data'] = $mData;
        $this->aCommands[] = $aAttributes;
        return $this;
    }

    /**
     * Add a response command to the array of commands that will be sent to the browser
     * Convert all attributes, excepted integers, to string.
     *
     * @param array $aAttributes    Associative array of attributes that will describe the command
     * @param mixed $mData    The data to be associated with this command
     *
     * @return ResponseInterface
     */
    public function addCommand(array $aAttributes, $mData): ResponseInterface
    {
        $aAttributes = array_map(function($xAttribute) {
            return is_integer($xAttribute) ? $xAttribute : trim((string)$xAttribute, " \t");
        }, $aAttributes);
        return $this->addRawCommand($aAttributes, $mData);
    }

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
    protected function _addCommand(string $sName, array $aAttributes,
        $mData, bool $bRemoveEmpty = false): ResponseInterface
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
     * @return ResponseInterface
     */
    public function addPluginCommand(ResponsePlugin $xPlugin, array $aAttributes, $mData): ResponseInterface
    {
        $aAttributes['plg'] = $xPlugin->getName();
        return $this->addCommand($aAttributes, $mData);
    }

    /**
     * Convert this response to a PSR7 response object
     *
     * @return PsrResponseInterface
     */
    public function toPsr(): PsrResponseInterface
    {
        $xPsrResponse = $this->xPsr17Factory->createResponse(200);
        if($this->xRequest->getMethod() === 'GET')
        {
            $xPsrResponse = $xPsrResponse
                ->withHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT')
                ->withHeader('Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT')
                ->withHeader('Cache-Control', 'no-cache, must-revalidate')
                ->withHeader('Pragma', 'no-cache');
        }
        return $xPsrResponse
            ->withHeader('content-type', $this->getContentType())
            ->withBody(Stream::create($this->getOutput()));
    }
}
