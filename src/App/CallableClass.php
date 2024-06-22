<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\Response;

class CallableClass extends AbstractCallable
{
    /**
     * @var Response
     */
    protected $response = null;

    /**
     * @inheritDoc
     */
    public function _initCallable(Container $di, CallableClassHelper $xCallableClassHelper)
    {
        $this->xCallableClassHelper = $xCallableClassHelper;
        $this->response = $di->getResponse();
    }

    /**
     * @inheritDoc
     */
    final protected function _response(): AjaxResponse
    {
        return $this->response;
    }
}
