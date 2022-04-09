<?php

namespace Jaxon\Plugin;

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
     * @param ServerRequestInterface $xRequest
     *
     * @return void
     */
    public function setTarget(ServerRequestInterface $xRequest);

    /**
     * Check if this plugin can process the current request
     *
     * Called by the <Jaxon\Plugin\RequestManager> when a request has been received to determine
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
     * Called by the <Jaxon\Plugin\RequestManager> when a request is being processed.
     * This will only occur when <Jaxon> has determined that the current request
     * is a valid (registered) jaxon enabled function via <jaxon->canProcessRequest>.
     *
     * @return ResponseInterface|null
     */
    public function processRequest(): ?ResponseInterface;
}
