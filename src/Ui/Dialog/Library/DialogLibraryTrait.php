<?php

/**
 * DialogLibraryTrait.php
 *
 * @package jaxon-dialogs
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Ui\Dialog\Library;

use Jaxon\Response\Response;
use Jaxon\Ui\Dialog\MessageInterface;

trait DialogLibraryTrait
{
    /**
     * The dialog library helper
     *
     * @var DialogLibraryHelper
     */
    protected $xHelper;

    /**
     * @var Response
     */
    protected $xResponse = null;

    /**
     * For MessageInterface, tells if the calls to the functions shall
     * add commands to the response or return the js code. By default, they add commands.
     *
     * @var bool
     */
    protected $bReturnCode = false;

    /**
     * Set the response to attach the messages to.
     *
     * @param Response $xResponse    Whether to return the code
     *
     * @return void
     */
    final public function setResponse(Response $xResponse)
    {
        $this->xResponse = $xResponse;
    }

    /**
     * Get the <Jaxon\Response\Response> object
     *
     * @return Response
     */
    final protected function response(): Response
    {
        return $this->xResponse;
    }

    /**
     * @param bool $bReturnCode
     *
     * @return MessageInterface
     */
    final public function setReturnCode(bool $bReturnCode): MessageInterface
    {
        $this->bReturnCode = $bReturnCode;
        return $this;
    }

    /**
     * Check if the library should return the js code or run it in the browser.
     *
     * @return bool
     */
    final protected function returnCode(): bool
    {
        return $this->bReturnCode;
    }
}
