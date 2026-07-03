<?php

namespace Jaxon\Plugin;

use Jaxon\Request\CallableAction;
use Psr\Http\Message\ServerRequestInterface;

interface RequestHandlerInterface
{
    /**
     * @param ServerRequestInterface $xRequest
     *
     * @return CallableAction
     */
    public function makeCallableAction(ServerRequestInterface $xRequest): CallableAction;

    /**
     * @return CallableAction|null
     */
    public function getCallableAction(): CallableAction|null;

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
     * @return void
     */
    public function processRequest(): void;
}
