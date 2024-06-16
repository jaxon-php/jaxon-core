<?php

/**
 * JqCall.php - A jQuery selector
 *
 * This class is used to create client side requests to the Jaxon functions and callable objects.
 *
 * When inserted into a Jaxon response, a JqCall object must be converted to the corresponding jQuery code.
 * Therefore, the JqCall class implements the JsonSerializable interface.
 *
 * When used as a parameter of a Jaxon call, the JqCall must be converted to Jaxon js call parameter.
 * Therefore, the JqCall class also implements the Jaxon\Script\Call\ParameterInterface interface.
 *
 * @package jaxon-jquery
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-jquery
 */

namespace Jaxon\Script;

use Jaxon\App\Dialog\DialogManager;
use Jaxon\Script\Call\Selector;
use Closure;

use function trim;

class JqCall extends AbstractCall
{
    /**
     * The dialog manager
     *
     * @var DialogManager
     */
    protected $xDialog;

    /**
     * The selector path
     *
     * @var string
     */
    protected $sPath;

    /**
     * The selector context
     *
     * @var mixed
     */
    protected $xContext;

    /**
     * The constructor.
     *
     * @param DialogManager $xDialog
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     * @param Closure|null $xExprCb
     */
    public function __construct(DialogManager $xDialog, string $sPath = '',
        $xContext = null, ?Closure $xExprCb = null)
    {
        parent::__construct($xDialog, $xExprCb);
        $this->sPath = trim($sPath, " \t");
        $this->xContext = $xContext;
    }

    /**
     * Get the json expression
     */
    protected function _expr(): JsExpr
    {
        $xJsExpr = new JsExpr($this->xDialog, new Selector($this->sPath, 'jq', $this->xContext));
        $this->xExprCb !== null && ($this->xExprCb)($xJsExpr);

        return $xJsExpr;
    }

    /**
     * Set an event handler on the first selected element
     *
     * @param string $sName
     * @param JsExpr $xHandler
     *
     * @return JsExpr
     */
    public function on(string $sName, JsExpr $xHandler): JsExpr
    {
        return $this->_expr()->on($sName, $xHandler);
    }

    /**
     * Set an "click" event handler on the first selected element
     *
     * @param JsExpr $xHandler
     *
     * @return JsExpr
     */
    public function click(JsExpr $xHandler): JsExpr
    {
        return $this->on('click', $xHandler);
    }
}
