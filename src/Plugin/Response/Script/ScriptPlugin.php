<?php

/**
 * JsPlugin.php
 *
 * Adds more js commands to the Jaxon response.
 *
 * @package jaxon-core
 * @copyright 2024 Thierry Feuzeu
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Response\Script;

use Jaxon\Script\Factory;
use Jaxon\Script\JqCall;
use Jaxon\Script\JsExpr;
use Jaxon\Script\JsCall;
use Jaxon\Plugin\AbstractResponsePlugin;
use Closure;

class ScriptPlugin extends AbstractResponsePlugin
{
    /**
     * @const The plugin name
     */
    const NAME = 'script';

    /**
     * @var Factory
     */
    private $xFactory;

    /**
     * @var Closure
     */
    private $xCallback;

    /**
     * The class constructor
     *
     * @param Factory $xFactory
     */
    public function __construct(Factory $xFactory)
    {
        $this->xFactory = $xFactory;
        $this->xCallback = function(JsExpr $xJsExpr) {
            // Add the newly created expression to the response
            $this->addCommand('script.exec', ['expr' => $xJsExpr]);;
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
        return $this->xFactory->jq($sPath, $xContext, $this->xCallback);
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
        return $this->xFactory->js($sObject, $this->xCallback);
    }
}
