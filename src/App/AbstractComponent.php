<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\ComponentResponse;

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
    public function _initCallable(Container $di, CallableClassHelper $xHelper)
    {
        $this->xHelper = $xHelper;
        // Each component must have its own reponse object.
        // A component can overrides another one. In this case,
        // its response is attached to the overriden component DOM node.
        $this->nodeResponse = $di->newComponentResponse($this->rq($this->overrides ?: ''));
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
     * Set the attached DOM node content with the component HTML code.
     *
     * @return AjaxResponse
     */
    abstract public function render(): AjaxResponse;

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
