<?php

namespace Jaxon\Script\Call;

/**
 * JsObjectCall.php
 *
 * Factory for a Javascript object.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\Script\Action\Attr;

class JsObjectCall extends AbstractJsCall
{
    /**
     * The class constructor
     *
     * @param string $sJsObject
     */
    public function __construct(protected string $sJsObject)
    {}

    /**
     * Get the call to add to the expression
     *
     * @return Attr
     */
    protected function _exprCall(): Attr
    {
        // If the value is '', return the js "window" object, otherwise, the corresponding js object.
        return Attr::get($this->sJsObject ?: 'window');
    }
}
