<?php

namespace Jaxon\Plugin\Contract;

use Jaxon\Request\Target;
use Jaxon\Response\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
     * @param ServerRequestInterface $xRequest
     *
     * @return bool
     */
    public static function canProcessRequest(ServerRequestInterface $xRequest): bool;

    /**
     * Process the current request
     *
     * Called by the <Jaxon\Plugin\PluginManager> when a request is being processed.
     * This will only occur when <Jaxon> has determined that the current request
     * is a valid (registered) jaxon enabled function via <jaxon->canProcessRequest>.
     *
     * @param ServerRequestInterface $xRequest
     *
     * @return ResponseInterface|null
     */
    public function processRequest(ServerRequestInterface $xRequest): ?ResponseInterface;
}
