<?php

namespace Jaxon\Plugin;

use Jaxon\Response\Response as JaxonResponse;

interface ResponsePluginInterface
{
    /**
     * Set the <Jaxon\Response\Response> object
     *
     * @param JaxonResponse $xResponse    The response
     *
     * @return void
     */
    public function setResponse(JaxonResponse $xResponse);

    /**
     * Get the <Jaxon\Response\Response> object
     *
     * @return JaxonResponse|null
     */
    public function response(): ?JaxonResponse;
}
