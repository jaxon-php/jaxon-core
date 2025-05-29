<?php

namespace Jaxon\App\View;

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
use Jaxon\Script\JxnCall;

use function count;
use function htmlentities;
use function is_a;
use function is_array;
use function is_string;
use function Jaxon\rq;
use function json_encode;
use function trim;

class HtmlAttrHelper
{
    /**
     * @var string
     */
    private $sPaginationComponent;

    /**
     * The constructor
     *
     * @param ComponentContainer $cdi
     */
    public function __construct(protected ComponentContainer $cdi)
    {
        $this->sPaginationComponent = rq(Pagination::class)->_class();
    }

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
        return is_a($xComponent, NodeComponent::class) ? (string)$xComponent->html() : '';
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
        return 'jxn-bind="' . $this->sPaginationComponent . '" jxn-item="' . $xJsCall->_class() . '"';
    }

    /**
     * Set a node as a target for event handler definitions
     *
     * @param string $name
     *
     * @return string
     */
    public function target(string $name = ''): string
    {
        return 'jxn-target="' . trim($name) . '"';
    }

    /**
     * @param array $on
     *
     * @return bool
     */
    private function checkOn(array $on)
    {
        // Only accept arrays of 2 entries.
        $count = count($on);
        if($count !== 2)
        {
            return false;
        }

        // Only accept arrays with int index from 0, and string value.
        for($i = 0; $i < $count; $i++)
        {
            if(!isset($on[$i]) || !is_string($on[$i]))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the event handler attributes
     *
     * @param string $select
     * @param string $event
     * @param string $attr
     * @param JsExpr $xJsExpr
     *
     * @return string
     */
    private function eventAttr(string $select, string $event, string $attr, JsExpr $xJsExpr): string
    {
        $sCall = htmlentities(json_encode($xJsExpr->jsonSerialize()));

        return "$attr=\"$event\" jxn-call=\"$sCall\"" .
            ($select !== '' ? "jxn-select=\"$select\" " : '');
    }

    /**
     * Set an event handler with the "on" keywork
     *
     * @param string|array $on
     * @param JsExpr $xJsExpr
     *
     * @return string
     */
    public function on(string|array $on, JsExpr $xJsExpr): string
    {
        $select = '';
        $event = $on;
        if(is_array($on))
        {
            if(!$this->checkOn($on))
            {
                return '';
            }

            $select = $on[0];
            $event = $on[1];
        }

        return $this->eventAttr(trim($select), trim($event), 'jxn-on', $xJsExpr);
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
     * Set an event handler with the "event" keywork
     *
     * @param array $on
     * @param JsExpr $xJsExpr
     *
     * @return string
     */
    public function event(array $on, JsExpr $xJsExpr): string
    {
        if(!$this->checkOn($on))
        {
            return '';
        }

        return $this->eventAttr(trim($on[0]), trim($on[1]), 'jxn-event', $xJsExpr);
    }
}
