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

use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AbstractResponsePlugin;
use Jaxon\Response\NodeResponse;
use Jaxon\Script\CallFactory;
use Jaxon\Script\Call\AbstractCall;
use Jaxon\Script\Call\JqSelectorCall;
use Jaxon\Script\Call\JsObjectCall;
use Jaxon\Script\Call\JsSelectorCall;
use Jaxon\Script\Call\JxnCall;
use Jaxon\Script\JsExpr;

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
     * @template Call of AbstractCall
     * @param Call $xCall
     *
     * @return Call
     */
    private function setCallback(AbstractCall $xCall): AbstractCall
    {
        // The closure needs to capture the response object the script plugin is called with.
        // So the current response is read and passed to the closure.
        $xResponse = $this->response();
        $xCall->_cb(function(JsExpr $xJsExpr) use($xResponse) {
            // Add the newly created expression to the response
            $aOptions = [
                'expr' => $xJsExpr,
                'context' => is_a($xResponse, NodeResponse::class) ?
                    ['component' => true] : [],
            ];
            $xResponse->addCommand('script.exec.expr', $aOptions)
                ->setOption('plugin', $this->getName());
        });
        return $xCall;
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
     * Get a factory for a registered class.
     *
     * @param string $sClassName
     *
     * @return JxnCall|null
     * @throws SetupException
     */
    public function rq(string $sClassName = ''): ?JxnCall
    {
        /*
         * The provided closure will be called each time a js expression is created with this factory,
         * with the expression as the only parameter.
         * It is currently used to attach the expression to a Jaxon response.
         */
        return $this->setCallback($this->xFactory->rq($sClassName));
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
        /*
         * The provided closure will be called each time a js expression is created with this factory,
         * with the expression as the only parameter.
         * It is currently used to attach the expression to a Jaxon response.
         */
        return $this->setCallback($this->xFactory->jq($sPath, $xContext));
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
        /*
         * The provided closure will be called each time a js expression is created with this factory,
         * with the expression as the only parameter.
         * It is currently used to attach the expression to a Jaxon response.
         */
        return $this->setCallback($this->xFactory->jo($sObject));
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
        /*
         * The provided closure will be called each time a js expression is created with this factory,
         * with the expression as the only parameter.
         * It is currently used to attach the expression to a Jaxon response.
         */
        return $this->setCallback($this->xFactory->je($sElementId));
    }
}
