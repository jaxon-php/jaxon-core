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
use Jaxon\JsCall\JqFactory;
use Jaxon\JsCall\JsExpr;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\Response\DataBag\DataBagContext;
use Jaxon\Plugin\Response\DataBag\DataBagPlugin;
use Jaxon\Plugin\Response\JQuery\JQueryPlugin;
use Jaxon\Plugin\Response\Pagination\Paginator;
use Jaxon\Plugin\Response\Pagination\PaginatorPlugin;
use Jaxon\Plugin\ResponsePlugin;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface as PsrRequestInterface;
use Closure;

use function array_shift;
use function func_get_args;
use function gmdate;
use function json_encode;
use function trim;

abstract class AjaxResponse extends AbstractResponse
{
    /**
     * @var PsrRequestInterface
     */
    protected $xRequest;

    /**
     * @var PluginManager
     */
    protected $xPluginManager;

    /**
     * @var DialogManager
     */
    protected $xDialogManager;

    /**
     * The constructor
     *
     * @param ResponseManager $xManager
     * @param Psr17Factory $xPsr17Factory
     * @param PsrRequestInterface $xRequest
     * @param PluginManager $xPluginManager
     * @param DialogManager $xDialogManager
     */
    public function __construct(ResponseManager $xManager, Psr17Factory $xPsr17Factory,
        PsrRequestInterface $xRequest, PluginManager $xPluginManager, DialogManager $xDialogManager)
    {
        parent::__construct($xManager, $xPsr17Factory);
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
        return json_encode(['jxn' => ['commands' => $this->xManager->getCommands()]]);
    }

    /**
     * Add a command to call the specified javascript function with the given (optional) parameters
     *
     * @param string $sFunc    The name of the function to call
     *
     * @return self
     */
    public function call(string $sFunc): self
    {
        $aArgs = func_get_args();
        array_shift($aArgs);
        $this->addCommand('script.call', ['func' => $this->str($sFunc),'args' => $aArgs]);
        return $this;
    }

    /**
     * Add a command to execute the specified json expression
     *
     * @param JsExpr $xJsExpr    The json expression to execute
     *
     * @return self
     */
    public function exec(JsExpr $xJsExpr): self
    {
        $this->addCommand('script.exec', ['expr' => $xJsExpr]);
        return $this;
    }

    /**
     * Create a new response
     *
     * @return AjaxResponse
     */
    abstract protected function newResponse(): AjaxResponse;

    /**
     * Response command that prompts user with [ok] [cancel] style message box
     *
     * The provided closure will be called with a response object as unique parameter.
     * If the user clicks cancel, the response commands defined in the closure will be skipped.
     *
     * @param Closure $fConfirm    A closure that defines the commands that can be skipped
     * @param string $sQuestion  The question to ask to the user
     * @param array $aArgs       The arguments for the placeholders in the question
     *
     * @return self
     */
    public function confirm(Closure $fConfirm, string $sQuestion, array $aArgs = []): self
    {
        $xResponse = $this->newResponse();
        $fConfirm($xResponse);
        if($xResponse->nCommandCount > 0)
        {
            // The confirm command must be inserted before the commands to be confirmed.
            $this->insertCommand('script.confirm', [
                'count' => $xResponse->nCommandCount,
                'question' => $this->dialog()->confirm($this->str($sQuestion), $aArgs),
            ], $xResponse->nCommandCount);
        }
        return $this;
    }

    /**
     * Add a command to display an alert message to the user
     *
     * @param string $sMessage    The message to be displayed
     * @param array $aArgs      The arguments for the placeholders in the message
     *
     * @return self
     */
    public function alert(string $sMessage, array $aArgs = []): self
    {
        $this->addCommand('dialog.message', $this->dialog()->info($this->str($sMessage), $aArgs));
        return $this;
    }

    /**
     * Add a command to display a debug message to the user
     *
     * @param string $sMessage    The message to be displayed
     *
     * @return self
     */
    public function debug(string $sMessage): self
    {
        $this->addCommand('script.debug', ['message' => $this->str($sMessage)]);
        return $this;
    }

    /**
     * Add a command to ask the browser to navigate to the specified URL
     *
     * @param string $sURL    The relative or fully qualified URL
     * @param integer $nDelay    Number of seconds to delay before the redirect occurs
     *
     * @return self
     */
    public function redirect(string $sURL, int $nDelay = 0): self
    {
        $this->addCommand('script.redirect', [
            'delay' => $nDelay,
            'url' => $this->xPluginManager->getParameterReader()->parseUrl($sURL),
        ]);
        return $this;
    }

    /**
     * Add a command to make Jaxon to pause execution of the response commands,
     * returning control to the browser so it can perform other commands asynchronously.
     *
     * After the specified delay, Jaxon will continue execution of the response commands.
     *
     * @param integer $tenths    The number of 1/10ths of a second to sleep
     *
     * @return self
     */
    public function sleep(int $tenths): self
    {
        $this->addCommand('script.sleep', ['duration' => $tenths]);
        return $this;
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
        $xResponsePlugin = $this->xPluginManager->getResponsePlugin($sName);
        if($xResponsePlugin !== null)
        {
            $xResponsePlugin->setResponse($this);
        }
        return $xResponsePlugin;
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
     * Create a JQuery selector expression, and link it to the current response.
     *
     * This is a shortcut to the JQuery plugin.
     *
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     *
     * @return JqFactory
     */
    public function jq(string $sPath = '', $xContext = null): JqFactory
    {
        /** @var JQueryPlugin */
        $xPlugin = $this->plugin('jquery');
        return $xPlugin->jq($sPath, $xContext);
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
        $xPlugin = $this->plugin('bags');
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