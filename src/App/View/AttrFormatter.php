<?php

namespace Jaxon\App\View;

/**
 * AttrFormatter.php
 *
 * Formatter for Jaxon custom HTML attributes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Component;
use Jaxon\Di\ClassContainer;
use Jaxon\Script\JsExpr;
use Jaxon\Script\JxnCall;

use function count;
use function htmlentities;
use function is_a;
use function is_array;
use function is_string;
use function json_encode;
use function trim;

class AttrFormatter
{
    /**
     * The constructor
     *
     * @param ClassContainer $cls
     */
    public function __construct(protected ClassContainer $cls)
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

        $xCallable = $this->cls->makeRegisteredObject($sClassName);
        return is_a($xCallable, Component::class) ? $xCallable->html() : '';
    }

    /**
     * Attach a component to a DOM node
     *
     * @param JxnCall $xJsCall
     * @param string $item
     *
     * @return string
     */
    public function show(JxnCall $xJsCall, string $item = ''): string
    {
        $item = trim($item);
        return 'jxn-show="' . $xJsCall->_class() . (!$item ? '"' : '" jxn-item="' . $item . '"');
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
        return count($on) === 2 && isset($on[0]) && isset($on[1])
            && is_string($on[0]) && is_string($on[1]);
    }

    /**
     * Set an event handler
     *
     * @param string|array $on
     * @param JsExpr $xJsExpr
     * @param array $options
     *
     * @return string
     */
    public function on(string|array $on, JsExpr $xJsExpr, array $options = []): string
    {
        $select = '';
        $event = $on;
        if(is_array($on))
        {
            if(!$this->checkOn($on))
            {
                return '';
            }
            $select = trim($on[0]);
            $event = $on[1];
        }
        $event = trim($event);

        return ($select !== '' ? 'jxn-select="' . $select . '" ' : '') .
            (isset($options['target']) ? 'jxn-event="' : 'jxn-on="') . $event .
            '" jxn-call="' . htmlentities(json_encode($xJsExpr->jsonSerialize())) . '"';
    }
}
