<?php

namespace Jaxon\App\View\Helper;

/**
 * EventAttr.php
 *
 * Wrapper for the event handler custom HTML attributes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\Script\JsExpr;

use function count;
use function htmlentities;
use function json_encode;
use function trim;

class EventAttr
{
    /**
     * @var array
     */
    private array $aHandlers = [];

    /**
     * The constructor
     *
     * @param string $sSelector
     */
    public function __construct(private string $sSelector)
    {}

    /**
     * Set a selector for the next event handler
     *
     * @param string $sSelector
     *
     * @return self
     */
    public function select(string $sSelector): self
    {
        $this->sSelector = trim($sSelector);
        return $this;
    }

    /**
     * Set an event handler with the "on" keyword
     *
     * @param string $event
     * @param JsExpr $xJsExpr
     *
     * @return self
     */
    public function on(string $event, JsExpr $xJsExpr): self
    {
        if($this->sSelector === '')
        {
            return $this;
        }

        $this->aHandlers[] = [
            'select' => $this->sSelector,
            'event' => trim($event),
            'handler' => $xJsExpr,
        ];
        $this->sSelector = '';
        return $this;
    }

    /**
     * Shortcut to set a click event handler
     *
     * @param JsExpr $xJsExpr
     *
     * @return self
     */
    public function click(JsExpr $xJsExpr): self
    {
        return $this->on('click', $xJsExpr);
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString(): string
    {
        // No output if no handler is defined.
        return count($this->aHandlers) === 0 ? '' : 'jxn-event="' .
            htmlentities(json_encode($this->aHandlers)) . '"';
    }
}
