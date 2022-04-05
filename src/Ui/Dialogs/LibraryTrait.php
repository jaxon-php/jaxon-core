<?php

/**
 * LibraryTrait.php - Trait for alert messages.
 *
 * @package jaxon-dialogs
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Ui\Dialogs;

use Jaxon\Response\Response;

trait LibraryTrait
{
    /**
     * @var Response
     */
    protected $xResponse = null;

    /**
     * @var bool
     */
    protected $bReturn = false;

    /**
     * Set the response to attach the messages to.
     *
     * @param Response $xResponse    Whether to return the code
     *
     * @return void
     */
    public function setResponse(Response $xResponse)
    {
        $this->xResponse = $xResponse;
    }

    /**
     * Get the <Jaxon\Response\Response> object
     *
     * @return Response
     */
    final public function response(): Response
    {
        return $this->xResponse;
    }

    /**
     * Set the library to return the javascript code or run it in the browser.
     *
     * @param bool $bReturn    Whether to return the code
     *
     * @return void
     */
    public function setReturn(bool $bReturn)
    {
        $this->bReturn = $bReturn;
    }

    /**
     * Check if the library should return the js code or run it in the browser.
     *
     * @return bool
     */
    public function getReturn(): bool
    {
        return $this->bReturn;
    }
}
