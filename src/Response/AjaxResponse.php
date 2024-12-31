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

use Jaxon\Exception\AppException;
use Jaxon\Script\JqCall;
use Jaxon\Script\JsExpr;
use Jaxon\Script\JsCall;
use Jaxon\Plugin\Response\DataBag\DataBagContext;
use Jaxon\Plugin\Response\DataBag\DataBagPlugin;
use Jaxon\Plugin\Response\Pagination\Paginator;
use Jaxon\Plugin\Response\Pagination\PaginatorPlugin;
use Jaxon\Plugin\Response\Psr\PsrPlugin;
use Jaxon\Plugin\Response\Script\ScriptPlugin;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Closure;

use function array_shift;
use function func_get_args;
use function json_encode;

abstract class AjaxResponse extends AbstractResponse
{
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
        $this->xManager->addCommand('script.exec.call',
            ['func' => $this->str($sFunc), 'args' => $aArgs]);
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
        $this->xManager->addCommand('script.exec.expr', ['expr' => $xJsExpr]);
        return $this;
    }

    /**
     * Response command that prompts user with [ok] [cancel] style message box
     *
     * The provided closure will be called with a response object as unique parameter.
     * If the user clicks cancel, the response commands defined in the closure will be skipped.
     *
     * @param Closure $fConfirm  A closure that defines the commands that can be skipped
     * @param string $sQuestion  The question to ask to the user
     * @param array $aArgs       The arguments for the placeholders in the question
     *
     * @throws AppException
     *
     * @return self
     */
    public function confirm(Closure $fConfirm, string $sQuestion, array $aArgs = []): self
    {
        $this->xManager->addConfirmCommand('script.confirm',
            fn() => $fConfirm($this), $sQuestion, $aArgs);
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
        $this->xManager->addAlertCommand('dialog.alert.show', $sMessage, $aArgs);
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
        $this->xManager->addCommand('script.debug', ['message' => $this->str($sMessage)]);
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
        $this->xManager->addCommand('script.redirect', [
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
        $this->xManager->addCommand('script.sleep', ['duration' => $tenths]);
        return $this;
    }

    /**
     * Create a JQuery selector expression, and link it to the current response.
     *
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     *
     * @return JqCall
     */
    public function jq(string $sPath = '', $xContext = null): JqCall
    {
        /** @var ScriptPlugin */
        $xPlugin = $this->plugin('script');
        return $xPlugin->jq($sPath, $xContext);
    }

    /**
     * Create a js expression, and link it to the current response.
     *
     * @param string $sObject
     *
     * @return JsCall
     */
    public function js(string $sObject = ''): JsCall
    {
        /** @var ScriptPlugin */
        $xPlugin = $this->plugin('script');
        return $xPlugin->js($sObject);
    }

    /**
     * Shortcut to get the factory for calls to a global js object or function.
     *
     * @return JsCall
     */
    public function jw(): JsCall
    {
        return $this->js('w');
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
     * @param int $nPageNumber     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return Paginator
     */
    public function paginator(int $nPageNumber, int $nItemsPerPage, int $nTotalItems): Paginator
    {
        /** @var PaginatorPlugin */
        $xPlugin = $this->plugin('pg');
        return $xPlugin->paginator($nPageNumber, $nItemsPerPage, $nTotalItems);
    }

    /**
     * Convert this response to a PSR7 response object
     *
     * @return PsrResponseInterface
     */
    public function toPsr(): PsrResponseInterface
    {
        /** @var PsrPlugin */
        $xPlugin = $this->plugin('psr');
        return $xPlugin->ajax();
    }
}
