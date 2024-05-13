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

use Jaxon\App\Dialog\DialogManager;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\Response\DataBag\DataBagContext;
use Jaxon\Plugin\Response\DataBag\DataBagPlugin;
use Jaxon\Plugin\Response\JQuery\JQueryPlugin;
use Jaxon\Plugin\Response\Pagination\Paginator;
use Jaxon\Plugin\Response\Pagination\PaginatorPlugin;
use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Request\Js\Selector;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface as PsrRequestInterface;

use function gmdate;
use function json_encode;
use function trim;

class Response implements ResponseInterface
{
    use Traits\CommandTrait;
    use Traits\HtmlDomTrait;
    use Traits\ScriptTrait;

    /**
     * @var PluginManager
     */
    protected $xPluginManager;

    /**
     * @var DialogManager
     */
    protected $xDialogManager;

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
     * @param Psr17Factory $xPsr17Factory
     * @param PsrRequestInterface $xRequest
     * @param PluginManager $xPluginManager
     * @param DialogManager $xDialogManager
     */
    public function __construct(Psr17Factory $xPsr17Factory, PsrRequestInterface $xRequest,
        PluginManager $xPluginManager, DialogManager $xDialogManager)
    {
        $this->xPsr17Factory = $xPsr17Factory;
        $this->xRequest = $xRequest;
        $this->xPluginManager = $xPluginManager;
        $this->xDialogManager = $xDialogManager;
    }

    /**
     * @return DialogManager
     */
    protected function dialog(): DialogManager
    {
        return $this->xDialogManager;
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
        return json_encode(['jxn' => ['commands' => $this->aCommands]]);
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
     * Convert to string
     *
     * @param mixed $xData
     *
     * @return string
     */
    protected function str($xData): string
    {
        return trim((string)$xData, " \t\n");
    }

    /**
     * Create a JQuery Selector, and link it to the current response.
     *
     * This is a shortcut to the JQuery plugin.
     *
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     *
     * @return Selector
     */
    public function jq(string $sPath = '', $xContext = null): Selector
    {
        /** @var JQueryPlugin */
        $xPlugin = $this->plugin('pg');
        return $xPlugin->selector($sPath, $xContext);
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
        /** @var DataBagPlugin */
        $xPlugin = $this->plugin('pg');
        return $xPlugin->bag($sName);
    }

    /**
     * Render an HTML pagination control.
     *
     * @param int $nCurrentPage     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return Paginator
     */
    public function paginator(int $nCurrentPage, int $nItemsPerPage, int $nTotalItems): Paginator
    {
        /** @var PaginatorPlugin */
        $xPlugin = $this->plugin('pg');
        return $xPlugin->paginator($nCurrentPage, $nItemsPerPage, $nTotalItems);
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
