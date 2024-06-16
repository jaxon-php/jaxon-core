<?php

namespace Jaxon\Script;

/**
 * Factory.php
 *
 * Creates the factories for js calls to functions or selectors.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Dialog\DialogManager;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\CallableRegistry;
use Pimple\Container;
use Closure;

use function trim;

class Factory
{
    /**
     * @var CallableRegistry
     */
    private $xCallableRegistry;

    /**
     * @var DialogManager
     */
    private $xDialog;

    /**
     * @var Container
     */
    private $xContainer;

    /**
     * @var string
     */
    protected $sClassPrefix;

    /**
     * @var string
     */
    protected $sFunctionPrefix;

    /**
     * The constructor.
     *
     * @param CallableRegistry $xCallableRegistry
     * @param DialogManager $xDialog
     * @param string $sClassPrefix
     * @param string $sFunctionPrefix
     */
    public function __construct(CallableRegistry $xCallableRegistry, DialogManager $xDialog,
        string $sClassPrefix, string $sFunctionPrefix)
    {
        $this->xCallableRegistry = $xCallableRegistry;
        $this->xDialog = $xDialog;
        $this->sClassPrefix = $sClassPrefix;
        $this->sFunctionPrefix = $sFunctionPrefix;

        $this->xContainer = new Container();
        // Factory for function parameters
        $this->xContainer->offsetSet(ParameterFactory::class, function() {
            return new ParameterFactory();
        });
        // Factory for registered functions
        $this->xContainer->offsetSet(JsCall::class, function() {
            return new JsCall($this->xDialog, $this->sFunctionPrefix);
        });
    }

    /**
     * @param string $sClassName
     *
     * @return JsCall
     */
    private function getRqCall(string $sClassName): ?JsCall
    {
        if(!$this->xContainer->offsetExists($sClassName))
        {
            $this->xContainer->offsetSet($sClassName, function() use($sClassName) {
                if(!($xCallable = $this->xCallableRegistry->getCallableObject($sClassName)))
                {
                    return null;
                }
                $sJsObject = $this->sClassPrefix . $xCallable->getJsName();
                return new JsCall($this->xDialog, $sJsObject . '.');
            });
        }
        return $this->xContainer->offsetGet($sClassName);
    }

    /**
     * Get a factory for a js function call.
     *
     * @param string $sClassName
     *
     * @return JsCall|null
     * @throws SetupException
     */
    public function rq(string $sClassName = ''): ?JsCall
    {
        return $this->getRqCall(trim($sClassName) ?: JsCall::class);
    }

    /**
     * Get a factory for a js function call.
     *
     * @param string $sObject
     * @param Closure|null $xExprCb
     *
     * @return JsCall|null
     */
    public function js(string $sObject = '', ?Closure $xExprCb = null): ?JsCall
    {
        /*
         * The provided closure will be called each time a js expression is created with this factory,
         * with the expression as the only parameter.
         * It is currently used to attach the expression to a Jaxon response.
         */
        return new JsCall($this->xDialog, trim($sObject), $xExprCb);
    }

    /**
     * Get a factory for a JQuery selector.
     *
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     * @param Closure|null $xExprCb
     *
     * @return JqCall
     */
    public function jq(string $sPath = '', $xContext = null, ?Closure $xExprCb = null): JqCall
    {
        /*
         * The provided closure will be called each time a js expression is created with this factory,
         * with the expression as the only parameter.
         * It is currently used to attach the expression to a Jaxon response.
         */
        return new JqCall($this->xDialog, trim($sPath), $xContext, $xExprCb);
    }

    /**
     * Get the js call parameter factory.
     *
     * @return ParameterFactory
     */
    public function pm(): ParameterFactory
    {
        return $this->xContainer->offsetGet(ParameterFactory::class);
    }
}
