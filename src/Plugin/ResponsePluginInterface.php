<?php

namespace Jaxon\Plugin;

use Jaxon\Response\AbstractResponse;

interface ResponsePluginInterface
{
    /**
     * Get the attached response
     *
     * @return AbstractResponse|null
     */
    public function response(): ?AbstractResponse;

    /**
     * @param AbstractResponse $xResponse   The response
     *
     * @return ResponsePluginInterface
     */
    public function _init(AbstractResponse $xResponse): ResponsePluginInterface;
}
