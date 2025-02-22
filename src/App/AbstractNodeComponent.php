<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\ComponentHelper;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\ComponentResponse;

abstract class AbstractNodeComponent extends AbstractComponent
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
    public function _initComponent(Container $di, ComponentHelper $xHelper)
    {
        $this->xHelper = $xHelper;
        // Each component must have its own reponse object.
        // A component can override another one. In this case,
        // its response is attached to the overriden component DOM node.
        $this->nodeResponse = $di->newComponentResponse($this->rq($this->overrides ?: ''));
    }

    /**
     * @inheritDoc
     */
    final protected function ajaxResponse(): AjaxResponse
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
     * @return void
     */
    abstract public function render();

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
