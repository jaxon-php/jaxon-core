<?php

namespace Jaxon\Script;

/**
 * JxnCall.php
 *
 * Call to a Jaxon registered function or class.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

 use Jaxon\App\Dialog\DialogManager;

class JxnCall extends AbstractCall
{
    /**
     * The Jaxon function prefix
     *
     * @var string
     */
    protected $sPrefix;

    /**
     * The constructor.
     *
     * @param DialogManager $xDialog
     * @param string $sPrefix
     */
    public function __construct(DialogManager $xDialog, string $sPrefix)
    {
        $this->xDialog = $xDialog;
        $this->sPrefix = $sPrefix;
    }

    /**
     * Create a js expression
     *
     * @return JsExpr
     */
    protected function _expr(): JsExpr
    {
        return new JsExpr($this->xDialog);
    }

    /**
     * Add a call to a js function on the current object
     *
     * @param string  $sMethod
     * @param array  $aArguments
     *
     * @return JsExpr
     */
    public function __call(string $sMethod, array $aArguments): JsExpr
    {
        // Append the prefix to the method name
        return parent::__call($this->sPrefix . $sMethod, $aArguments);
    }

    /**
     * Get the js class name
     *
     * @return string
     */
    public function _class(): string
    {
        return '';
    }
}
