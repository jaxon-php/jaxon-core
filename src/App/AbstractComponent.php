<?php

namespace Jaxon\App;

use Jaxon\App\Cache\Cache;
use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\ComponentResponse;

use function get_class;

abstract class AbstractComponent extends AbstractCallable
{
    /**
     * @var ComponentResponse
     */
    protected $nodeResponse = null;

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
        $this->temp = $di->get(Cache::class);

        // A component can overrides another one. In this case,
        // its response is attached to the overriden component DOM node.
        $sClassName = $this->overrides ?: get_class($this);
        // Each component must have its own reponse object.
        $this->nodeResponse = $di->newComponentResponse($sClassName);
    }

    /**
     * @inheritDoc
     */
    final protected function _response(): AjaxResponse
    {
        return $this->nodeResponse;
    }

    /**
     * Get the component response
     *
     * @return ComponentResponse
     */
    final protected function node(): ComponentResponse
    {
        return $this->nodeResponse;
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
        $this->node()->item($item);

        return $this;
    }
}
