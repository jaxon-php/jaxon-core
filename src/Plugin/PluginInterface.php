<?php

namespace Jaxon\Plugin;

interface PluginInterface
{
    /**
     * Get a unique name to identify the plugin.
     *
     * @return string
     */
    public function getName(): string;
}
