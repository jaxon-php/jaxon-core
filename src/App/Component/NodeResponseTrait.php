<?php

namespace Jaxon\App\Component;

use Jaxon\Di\Container;
use Jaxon\Response\NodeResponse;

trait NodeResponseTrait
{
    /**
     * @var NodeResponse
     */
    protected readonly NodeResponse $nodeResponse;

    /**
     * @var string
     */
    protected string $overrides = '';

    /**
     * @param Container $di
     *
     * @return void
     */
    private function setNodeResponse(Container $di): void
    {
        // Each component must have its own reponse object.
        // A component can override another one. In this case,
        // its response is attached to the overriden component DOM node.
        $this->nodeResponse = $di->newNodeResponse($this->rq($this->overrides ?: ''));
    }

    /**
     * Get the component response
     *
     * @return NodeResponse
     */
    final protected function node(): NodeResponse
    {
        return $this->nodeResponse;
    }

    /**
     * Get the component response
     *
     * @return NodeResponse
     */
    final protected function nodeResponse(): NodeResponse
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
        $this->node()->item($this->factory()->helper()->extendValue('item', $item));
        return $this;
    }
}
