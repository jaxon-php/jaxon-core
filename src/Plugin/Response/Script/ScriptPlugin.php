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
use Jaxon\Response\NodeResponse;
use Jaxon\Script\CallFactory;
use Jaxon\Script\Call\JqSelectorCall;
use Jaxon\Script\Call\JsObjectCall;
use Jaxon\Script\Call\JsSelectorCall;
use Jaxon\Script\JsExpr;
use Closure;

use function is_a;

class ScriptPlugin extends AbstractResponsePlugin
{
    /**
     * @const The plugin name
     */
    public const NAME = 'script';

    /**
     * The class constructor
     *
     * @param CallFactory $xFactory
     */
    public function __construct(private CallFactory $xFactory)
    {}

    /**
     * @return Closure
     */
    private function getCallback(): Closure
    {
        // The closure needs to capture the response object the script plugin is called with.
        $xResponse = $this->response();
        return function(JsExpr $xJsExpr) use($xResponse) {
            // Add the newly created expression to the response
            $aOptions = [
                'expr' => $xJsExpr,
                'context' => is_a($xResponse, NodeResponse::class) ?
                    ['component' => true] : [],
            ];
            $xResponse->addCommand('script.exec.expr', $aOptions)
                ->setOption('plugin', $this->getName());
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
     * @return JqSelectorCall
     */
    public function jq(string $sPath = '', $xContext = null): JqSelectorCall
    {
        return $this->xFactory->jq($sPath, $xContext, $this->getCallback());
    }

    /**
     * Create a Javascript object expression, and link it to the current response.
     *
     * @param string $sObject
     *
     * @return JsObjectCall
     */
    public function jo(string $sObject = ''): JsObjectCall
    {
        return $this->xFactory->jo($sObject, $this->getCallback());
    }

    /**
     * Create a Javascript element selector expression, and link it to the current response.
     *
     * @param string $sElementId
     *
     * @return JsSelectorCall
     */
    public function je(string $sElementId = ''): JsSelectorCall
    {
        return $this->xFactory->je($sElementId, $this->getCallback());
    }
}
