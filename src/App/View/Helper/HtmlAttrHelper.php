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

use Jaxon\App\NodeComponent;
use Jaxon\App\PageComponent;
use Jaxon\Di\ComponentContainer;
use Jaxon\Script\CallFactory;
use Jaxon\Script\Call\JxnCall;
use Jaxon\Script\JsExpr;

use function array_reduce;
use function count;
use function htmlentities;
use function is_a;
use function is_string;
use function json_encode;
use function trim;

class HtmlAttrHelper
{
    /**
     * The constructor
     *
     * @param ComponentContainer $cdi
     * @param CallFactory $xCallFactory
     * @param string $sPaginationComponent
     */
    public function __construct(protected ComponentContainer $cdi,
        protected CallFactory $xCallFactory, private string $sPaginationComponent)
    {}

    /**
     * Get the component HTML code
     *
     * @param JxnCall|class-string $xJsCall
     *
     * @return string
     */
    public function html(JxnCall|string $xJsCall): string
    {
        $sClassName = is_string($xJsCall) ? trim($xJsCall) : $xJsCall->_class();
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
     * @param JxnCall|class-string $xJsCall
     * @param string $item
     *
     * @return string
     */
    public function bind(JxnCall|string $xJsCall, string $item = ''): string
    {
        if(is_string($xJsCall))
        {
            $xJsCall = $this->xCallFactory->rq($xJsCall);
            if($xJsCall === null)
            {
                return '';
            }
        }

        $item = trim($item);
        return 'jxn-bind="' . $xJsCall->_class() . (!$item ? '"' : '" jxn-item="' . $item . '"');
    }

    /**
     * @param JxnCall|PageComponent|class-string $xPaginated
     *
     * @return array
     */
    public function paginationAttributes(JxnCall|PageComponent|string $xPaginated): array
    {
        if(is_a($xPaginated, PageComponent::class))
        {
            /** @var PageComponent */
            $xComponent = $xPaginated;
            return $xComponent->paginationAttributes();
        }

        /** @var JxnCall */
        $xJsCall = is_string($xPaginated) ? $this->xCallFactory->rq($xPaginated) : $xPaginated;
        return $xJsCall === null ? ['', ''] : [$this->sPaginationComponent, $xJsCall->_class()];
    }

    /**
     * Attach the pagination component to a DOM node
     *
     * @param JxnCall|PageComponent|class-string $xPaginated
     *
     * @return string
     */
    public function pagination(JxnCall|PageComponent|string $xPaginated): string
    {
        [$sComponent, $sItem] = $this->paginationAttributes($xPaginated);
        return $sComponent === '' ? '' : "jxn-bind=\"$sComponent\" jxn-item=\"$sItem\"";
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

    /**
     * Get the HTML or Javascript ready code for a package
     *
     * @param string $sClass
     * @param string $sType
     *
     * @return string
     */
    public function package(string $sClass, string $sType = 'html'): string
    {
        if(($xPackage = jaxon()->package($sClass)) === null)
        {
            return '';
        }

        $sCode = trim(match($sType) {
            'html' => $xPackage->getHtml(),
            'inline' => $xPackage->getInlineScript(),
            'ready' => $xPackage->getReadyScript(),
            default => ''
        });

        return $sCode === '' || $sType !== 'ready' ? $sCode :
            // Call the ready code with the jaxon.dom.ready function.
            "jaxon.dom.ready(() => $sCode)";
    }
}
