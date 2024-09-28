<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\ComponentResponse;

use function get_class;

trait ComponentTrait
{
    /**
     * @var ComponentResponse
     */
    protected $response = null;

    /**
     * @var string
     */
    protected $overrides = '';

    /**
     * @inheritDoc
     */
    public function _initCallable(Container $di, CallableClassHelper $xCallableClassHelper)
    {
        $this->xCallableClassHelper = $xCallableClassHelper;

        // A component can overrides another one. In this case,
        // its response is attached to the overriden component DOM node.
        $sClassName = $this->overrides ?: get_class($this);
        // Each component must have its own reponse object.
        $this->response = $di->newComponentResponse($sClassName);
    }

    /**
     * @inheritDoc
     */
    final protected function _response(): AjaxResponse
    {
        return $this->response;
    }

    /**
     * Set the component item.
     *
     * @param string $item
     *
     * @return self
     */
    final public function item(string $item): self
    {
        $this->response->item($item);

        return $this;
    }
}
