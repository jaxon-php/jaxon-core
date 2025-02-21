<?php

namespace Jaxon\Script\Factory;

/**
 * CallFactory.php
 *
 * Creates calls to js functions and selectors.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\Di\ComponentContainer;
use Jaxon\Exception\SetupException;
use Jaxon\Script\JqCall;
use Jaxon\Script\JsCall;
use Jaxon\Script\JxnCall;
use Closure;

use function trim;

class CallFactory
{
    /**
     * The constructor.
     *
     * @param ComponentContainer $cdi
     * @param DialogCommand $xDialog
     */
    public function __construct(private ComponentContainer $cdi, private DialogCommand $xDialog)
    {}

    /**
     * Get a factory for a js function call.
     *
     * @param string $sClassName
     *
     * @return JxnCall|null
     * @throws SetupException
     */
    public function rq(string $sClassName = ''): ?JxnCall
    {
        return $this->cdi->getRequestFactory($sClassName);
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
        return new JsCall($this->xDialog, $xExprCb, trim($sObject, " \t"));
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
        return new JqCall($this->xDialog, $xExprCb, trim($sPath, " \t"), $xContext);
    }
}
