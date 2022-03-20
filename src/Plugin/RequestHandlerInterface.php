<?php

namespace Jaxon\Plugin;

use Jaxon\Request\Target;

interface RequestHandlerInterface
{
    /**
     * Get the target function or class and method
     *
     * @return Target|null
     */
    public function getTarget(): ?Target;

    /**
     * Check if this plugin can process the current request
     *
     * Called by the <Jaxon\Plugin\PluginManager> when a request has been received to determine
     * if the request is targeted to this request plugin.
     *
     * @return bool
     */
    public static function canProcessRequest(): bool;

    /**
     * Process the current request
     *
     * Called by the <Jaxon\Plugin\PluginManager> when a request is being processed.
     * This will only occur when <Jaxon> has determined that the current request
     * is a valid (registered) jaxon enabled function via <jaxon->canProcessRequest>.
     *
     * @return bool
     */
    public function processRequest(): bool;
}
