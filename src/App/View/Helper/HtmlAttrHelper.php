<?php

namespace Jaxon\App\View\Helper;

/**
 * HtmlAttrHelper.php
 *
 * Formatter for Jaxon custom HTML attributes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Component\Pagination;
use Jaxon\App\NodeComponent;
use Jaxon\Di\ComponentContainer;
use Jaxon\Script\JsExpr;
use Jaxon\Script\Call\JxnCall;

use function array_reduce;
use function count;
use function htmlentities;
use function is_a;
use function is_string;
use function Jaxon\rq;
use function json_encode;
use function trim;

class HtmlAttrHelper
{
    /**
     * @var string|null
     */
    private string|null $sPaginationComponent = null;

    /**
     * The constructor
     *
     * @param ComponentContainer $cdi
     */
    public function __construct(protected ComponentContainer $cdi)
    {}

    /**
     * Get the component HTML code
     *
     * @param JxnCall $xJsCall
     *
     * @return string
     */
    public function html(JxnCall $xJsCall): string
    {
        $sClassName = $xJsCall->_class();
        if(!$sClassName)
        {
            return '';
        }

        $xComponent = $this->cdi->makeComponent($sClassName);
        return is_a($xComponent, NodeComponent::class) ?
            (string)$xComponent->html() : '';
    }

    /**
     * Attach a component to a DOM node
     *
     * @param JxnCall $xJsCall
     * @param string $item
     *
     * @return string
     */
    public function bind(JxnCall $xJsCall, string $item = ''): string
    {
        $item = trim($item);
        return 'jxn-bind="' . $xJsCall->_class() . (!$item ? '"' : '" jxn-item="' . $item . '"');
    }

    /**
     * Attach the pagination component to a DOM node
     *
     * @param JxnCall $xJsCall
     *
     * @return string
     */
    public function pagination(JxnCall $xJsCall): string
    {
        // The pagination is always rendered with the same Pagination component.
        $sComponent = $this->sPaginationComponent ?:
            ($this->sPaginationComponent = rq(Pagination::class)->_class());
        $sItem = $xJsCall->_class();
        return "jxn-bind=\"$sComponent\" jxn-item=\"$sItem\"";
    }

    /**
     * Set a selector for the next event handler
     *
     * @param string $sSelector
     *
     * @return EventAttr
     */
    public function select(string $sSelector): EventAttr
    {
        return new EventAttr($sSelector);
    }

    /**
     * Set an event handler
     *
     * @param string $event
     * @param JsExpr $xJsExpr
     *
     * @return string
     */
    public function on(string $event, JsExpr $xJsExpr): string
    {
        $event = trim($event);
        $sCall = htmlentities(json_encode($xJsExpr->jsonSerialize()));
        return "jxn-on=\"$event\" jxn-call=\"$sCall\"";
    }

    /**
     * Shortcut to set a click event handler
     *
     * @param JsExpr $xJsExpr
     *
     * @return string
     */
    public function click(JsExpr $xJsExpr): string
    {
        return $this->on('click', $xJsExpr);
    }

    /**
     * @param array $aHandler
     *
     * @return bool
     */
    private function eventIsValid(array $aHandler): bool
    {
        return count($aHandler) === 3 &&
            isset($aHandler[0]) && isset($aHandler[1]) && isset($aHandler[2]) &&
            is_string($aHandler[0]) && is_string($aHandler[1]) &&
            is_a($aHandler[2], JsExpr::class);
    }

    /**
     * @param array $aHandler
     * @param EventAttr|null $xAttr
     *
     * @return EventAttr|null
     */
    private function setEventHandler(array $aHandler, ?EventAttr $xAttr = null): EventAttr|null
    {
        if(!$this->eventIsValid($aHandler))
        {
            return $xAttr;
        }
        // The array content is valid.
        [$sSelector, $sEvent, $xJsExpr] = $aHandler;
        return !$xAttr ?
            $this->select($sSelector)->on($sEvent, $xJsExpr) :
            $xAttr->select($sSelector)->on($sEvent, $xJsExpr);
    }

    /**
     * Set an event handler
     *
     * @param array $aHandler
     *
     * @return string
     */
    public function event(array $aHandler): string
    {
        return $this->setEventHandler($aHandler)?->__toString() ?? '';
    }

    /**
     * Set event handlers
     *
     * @param array $aHandlers
     *
     * @return string
     */
    public function events(array $aHandlers): string
    {
        return array_reduce($aHandlers, fn(EventAttr|null $xAttr, array $aHandler)
            => $this->setEventHandler($aHandler, $xAttr), null)?->__toString() ?? '';
    }
}
