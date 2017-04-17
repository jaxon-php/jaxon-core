<?php

/**
 * Parameter.php - A parameter of a Jaxon request
 *
 * This class is used to create client side requests to the Jaxon functions and callable objects.
 *
 * @package jaxon-core
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Interfaces;

use Jaxon\Request\JsCall;

interface Parameter
{
    /**
     * Set the call this parameter is passed to.
     *
     * @return void
     */
    public function setCall(JsCall $xCall);

    /**
     * Generate the javascript code of the parameter.
     *
     * @return string
     */
    public function getScript();
}
