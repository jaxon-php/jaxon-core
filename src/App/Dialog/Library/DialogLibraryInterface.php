<?php

/**
 * DialogLibraryInterface.php
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-dialogs
 */

namespace Jaxon\App\Dialog\Library;

use Jaxon\Response\Response;

interface DialogLibraryInterface
{
    /**
     * Set the response to attach the messages to.
     *
     * @param Response $xResponse    Whether to return the code
     *
     * @return void
     */
    public function setResponse(Response $xResponse);

    /**
     * @param bool $bReturnCode
     *
     * @return void
     */
    public function setReturnCode(bool $bReturnCode);
}
