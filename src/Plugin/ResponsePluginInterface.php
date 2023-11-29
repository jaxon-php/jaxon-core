<?php

namespace Jaxon\Plugin;

use Jaxon\Response\ResponseInterface;

interface ResponsePluginInterface
{
    /**
     * Set the <Jaxon\Response\ResponseInterface> object
     *
     * @param ResponseInterface $xResponse    The response
     *
     * @return void
     */
    public function setResponse(ResponseInterface $xResponse);

    /**
     * Get the <Jaxon\Response\ResponseInterface> object
     *
     * @return ResponseInterface|null
     */
    public function response(): ?ResponseInterface;
}
