<?php

namespace Jaxon\JsCall;

/**
 * Factory.php
 *
 * Gives access to the factories.
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
    protected $xDialog;

    /**
     * @var ParameterFactory
     */
    protected $xParameterFactory;

    /**
     * @var JsFactory
     */
    protected $xRqFunctionFactory;

    /**
     * @var JsFactory
     */
    protected $xJsFunctionFactory;

    /**
     * @var JqFactory
     */
    protected $xJqThisFactory;

    /**
     * The constructor.
     *
     * @param CallableRegistry $xCallableRegistry
     * @param DialogManager $xDialog
     * @param ParameterFactory $xParameterFactory
     * @param string $sFunctionPrefix
     */
    public function __construct(CallableRegistry $xCallableRegistry, DialogManager $xDialog,
        ParameterFactory $xParameterFactory, string $sFunctionPrefix)
    {
        $this->xCallableRegistry = $xCallableRegistry;
        $this->xDialog = $xDialog;
        $this->xParameterFactory = $xParameterFactory;
        // Factory for registered functions
        $this->xRqFunctionFactory = new JsFactory($this->xDialog, $sFunctionPrefix);
        // Factory for global Js functions
        $this->xJsFunctionFactory = new JsFactory($this->xDialog);
        // Factory for global Js functions
        $this->xJqThisFactory = new JqFactory($this->xDialog);
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
            $this->xRqFunctionFactory :
            // Factory for calls to a Jaxon js class
            $this->xCallableRegistry->getJsFactory($sClassName);
    }

    /**
     * Get a factory for a js function call.
     *
     * @param string $sClassName
     *
     * @return JsFactory|null
     */
    public function js(string $sClassName = ''): ?JsFactory
    {
        $sClassName = trim($sClassName);
        return !$sClassName ?
            // Factory for calls to a js function
            $this->xJsFunctionFactory :
            // Factory for calls to a js class
            new JsFactory($this->xDialog, $sClassName);
    }

    /**
     * Get a factory for a JQuery selector.
     *
     * The returned element is not linked to any Jaxon response, so this function shall be used
     * to insert jQuery's code into a javascript function, or as a parameter of a Jaxon function call.
     *
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     *
     * @return JqFactory
     */
    public function jq(string $sPath = '', $xContext = null): JqFactory
    {
        $sPath = trim($sPath);
        return !$sPath ?
            // Factory for calls to the "this" jquery selector
            $this->xJqThisFactory :
            // Factory for calls to a jquery selector
            new JqFactory($this->xDialog, $sPath, $xContext);
    }

    /**
     * Get the js call parameter factory.
     *
     * @return ParameterFactory
     */
    public function pm(): ParameterFactory
    {
        return $this->xParameterFactory;
    }
}
