<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\ComponentResponse;

use function get_class;

abstract class PaginatorComponent extends AbstractCallable
{
    /**
     * @var ComponentResponse
     */
    protected $response = null;

    /**
     * @inheritDoc
     */
    public function _initCallable(Container $di, CallableClassHelper $xCallableClassHelper)
    {
        $this->xCallableClassHelper = $xCallableClassHelper;
        // Each component must have its own reponse object.
        $this->response = $di->newComponentResponse(get_class($this));
    }

    /**
     * @inheritDoc
     */
    final protected function _response(): AjaxResponse
    {
        return $this->response;
    }
}
