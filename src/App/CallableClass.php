<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\Response;

use function get_class;

class CallableClass extends AbstractCallable
{
    /**
     * @var Response
     */
    protected $response = null;

    /**
     * @inheritDoc
     */
    final public function _initCallable(Container $di)
    {
        $sClassName = get_class($this);
        $this->xCallableClassHelper = new CallableClassHelper($di, $sClassName);
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
