<?php

namespace Jaxon\Plugin;

use Jaxon\Response\AjaxResponse;

interface ResponsePluginInterface
{
    /**
     * Set the <Jaxon\Response\AjaxResponse> object
     *
     * @param AjaxResponse $xResponse    The response
     *
     * @return void
     */
    public function setResponse(AjaxResponse $xResponse);

    /**
     * Get the <Jaxon\Response\AjaxResponse> object
     *
     * @return AjaxResponse|null
     */
    public function response(): ?AjaxResponse;
}
