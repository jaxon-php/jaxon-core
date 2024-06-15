<?php

namespace Jaxon\Plugin;

use Jaxon\Response\AbstractResponse;

interface ResponsePluginInterface
{
    /**
     * Set the <Jaxon\Response\AbstractResponse> object
     *
     * @param AbstractResponse $xResponse    The response
     *
     * @return void
     */
    public function setResponse(AbstractResponse $xResponse);

    /**
     * Get the <Jaxon\Response\AbstractResponse> object
     *
     * @return AbstractResponse|null
     */
    public function response(): ?AbstractResponse;
}
