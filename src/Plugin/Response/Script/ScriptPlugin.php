<?php

/**
 * ScriptPlugin.php
 *
 * Adds Javascript selector and function call commands to the Jaxon response.
 *
 * @package jaxon-core
 * @copyright 2024 Thierry Feuzeu
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Response\Script;

use Jaxon\Plugin\AbstractResponsePlugin;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\NodeResponse;
use Jaxon\Script\Factory\CallFactory;
use Jaxon\Script\JqCall;
use Jaxon\Script\JsExpr;
use Jaxon\Script\JsCall;
use Closure;

use function is_a;

class ScriptPlugin extends AbstractResponsePlugin
{
    /**
     * @const The plugin name
     */
    const NAME = 'script';

    /**
     * The class constructor
     *
     * @param CallFactory $xFactory
     */
    public function __construct(private CallFactory $xFactory)
    {}

    /**
     * @param JsExpr $xJsExpr
     * @param AjaxResponse $xResponse
     *
     * @return void
     */
    private function _addCommand(JsExpr $xJsExpr, AjaxResponse $xResponse)
    {
        // Add the newly created expression to the response
        $xResponse
            ->addCommand('script.exec.expr', [
                'expr' => $xJsExpr,
                'context' => [
                    'component' => is_a($xResponse, NodeResponse::class),
                ],
            ])
            ->setOption('plugin', $this->getName());
    }

    /**
     * @return Closure
     */
    private function getCallback(): Closure
    {
        // The closure needs to capture the response object the script plugin is called with.
        $xResponse = $this->response();
        return function(JsExpr $xJsExpr) use($xResponse) {
            $this->_addCommand($xJsExpr, $xResponse);
        };
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        // Use the version number as hash
        return '5.0.0';
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
        return $this->xFactory->jq($sPath, $xContext, $this->getCallback());
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
        return $this->xFactory->js($sObject, $this->getCallback());
    }
}
