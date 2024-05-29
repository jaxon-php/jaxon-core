<?php

namespace Jaxon\JsCall;

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
        $this->xContainer->offsetSet(JsFactory::class . '_RqFactory', function() {
            return new JsFactory($this->xDialog, $this->sFunctionPrefix);
        });
        // Factory for global Js functions
        $this->xContainer->offsetSet(JsFactory::class, function() {
            return new JsFactory($this->xDialog);
        });
        // Factory for global Js functions
        $this->xContainer->offsetSet(JqFactory::class, function() {
            return new JqFactory($this->xDialog);
        });
    }

    /**
     * @param string $sClassName
     *
     * @return JsFactory
     */
    private function getRqFactory(string $sClassName): ?JsFactory
    {
        $sKey = $sClassName . '_RqFactory';
        if(!$this->xContainer->offsetExists($sKey))
        {
            $this->xContainer->offsetSet($sKey, function() use($sClassName) {
                if(!($xCallable = $this->xCallableRegistry->getCallableObject($sClassName)))
                {
                    return null;
                }
                $sJsObject = $this->sClassPrefix . $xCallable->getJsName();
                return new JsFactory($this->xDialog, $sJsObject . '.');
            });
        }
        return $this->xContainer->offsetGet($sKey);
    }

    /**
     * Get a factory for a js function call.
     *
     * @param string $sClassName
     *
     * @return JsFactory|null
     * @throws SetupException
     */
    public function rq(string $sClassName = ''): ?JsFactory
    {
        $sClassName = trim($sClassName);
        return !$sClassName ?
            // Factory for calls to a Jaxon js function
            $this->xContainer->offsetGet(JsFactory::class . '_RqFactory') :
            // Factory for calls to a Jaxon js class
            $this->getRqFactory($sClassName);
    }

    /**
     * @param string $sObject
     *
     * @return JsFactory
     */
    private function getJsFactory(string $sObject): JsFactory
    {
        $sKey = JsFactory::class . "_$sObject";
        if(!$this->xContainer->offsetExists($sKey))
        {
            $this->xContainer->offsetSet($sKey, function() use($sObject) {
                return new JsFactory($this->xDialog, $sObject);
            });
        }
        return $this->xContainer->offsetGet($sKey);
    }

    /**
     * Get a factory for a js function call.
     *
     * @param string $sObject
     *
     * @return JsFactory|null
     */
    public function js(string $sObject = ''): ?JsFactory
    {
        $sObject = trim($sObject);
        return !$sObject ?
            // Factory for calls to a global js function
            $this->xContainer->offsetGet(JsFactory::class) :
            // Factory for calls to a function of js object
            $this->getJsFactory($sObject);
    }

    /**
     * Get a factory for a JQuery selector.
     *
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     * @param Closure|null $xExprCb
     *
     * @return JqFactory
     */
    public function jq(string $sPath = '', $xContext = null, ?Closure $xExprCb = null): JqFactory
    {
        /*
         * The provided closure will be called each time a js expression is created with this factory,
         * with the expression as the only parameter.
         * It is currently used to attch the expression to a Jaxon response.
         */
        $sPath = trim($sPath);
        return !$sPath ?
            // Factory for calls to the "this" jquery selector
            $this->xContainer->offsetGet(JqFactory::class) :
            // Factory for calls to a jquery selector
            new JqFactory($this->xDialog, $sPath, $xContext, $xExprCb);
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
