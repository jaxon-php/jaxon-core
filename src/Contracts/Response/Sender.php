<?php

/**
 * Sender.php - Interface for sending Ajax response
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Contracts\Response;

interface Sender
{
    /**
     * Send the Jaxon response back to the browser.
     *
     * @param  $sCode        The HTTP Response code
     *
     * @return mixed
     */
    public function sendResponse($sCode = '200');
}
