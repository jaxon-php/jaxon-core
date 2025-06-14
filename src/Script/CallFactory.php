<?php

namespace Jaxon\Script;

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
use Jaxon\Script\Call\JsSelectorCall;
use Jaxon\Script\Call\JqSelectorCall;
use Jaxon\Script\Call\JsObjectCall;
use Jaxon\Script\Call\JxnCall;
use Jaxon\Script\JsExpr;
use Closure;

use function trim;

class CallFactory
{
    /**
     * The constructor.
     *
     * @param ComponentContainer $cdi
     * @param DialogCommand $xDialogCommand
     */
    public function __construct(private ComponentContainer $cdi, DialogCommand $xDialogCommand)
    {
        JsExpr::setDialogCommand($xDialogCommand);
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
        return $this->cdi->getRequestFactory(trim($sClassName));
    }

    /**
     * Get a factory for a Javascript object.
     *
     * @param string $sObject
     * @param Closure|null $xExprCb
     *
     * @return JsObjectCall|null
     */
    public function jo(string $sObject = '', ?Closure $xExprCb = null): ?JsObjectCall
    {
        /*
         * The provided closure will be called each time a js expression is created with this factory,
         * with the expression as the only parameter.
         * It is currently used to attach the expression to a Jaxon response.
         */
        return new JsObjectCall($xExprCb, trim($sObject, " \t"));
    }

    /**
     * Get a factory for a JQuery selector.
     *
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     * @param Closure|null $xExprCb
     *
     * @return JqSelectorCall
     */
    public function jq(string $sPath = '', $xContext = null, ?Closure $xExprCb = null): JqSelectorCall
    {
        /*
         * The provided closure will be called each time a js expression is created with this factory,
         * with the expression as the only parameter.
         * It is currently used to attach the expression to a Jaxon response.
         */
        return new JqSelectorCall($xExprCb, trim($sPath, " \t"), $xContext);
    }

    /**
     * Get a factory for a Javascript element selector.
     *
     * @param string $sElementId    The DOM element id
     * @param Closure|null $xExprCb
     *
     * @return JsSelectorCall
     */
    public function je(string $sElementId = '', ?Closure $xExprCb = null): JsSelectorCall
    {
        /*
         * The provided closure will be called each time a js expression is created with this factory,
         * with the expression as the only parameter.
         * It is currently used to attach the expression to a Jaxon response.
         */
        return new JsSelectorCall($xExprCb, trim($sElementId, " \t"));
    }
}
