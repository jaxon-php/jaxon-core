<?php

namespace Jaxon\App\Component;

use Jaxon\Di\Container;
use Jaxon\Response\NodeResponse;
use Closure;

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
     * @var array
     */
    private array $extensions = [];

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
     * @param string $target
     * @param Closure $extension
     *
     * @return self
     */
    final protected function extend(string $target, Closure $extension): self
    {
        if($target === 'html' || $target === 'item')
        {
            $this->extensions[$target] ??= [];
            $this->extensions[$target][] = $extension;
        }

        // All other target values are ignored.
        return $this;
    }

    /**
     * @param string $target
     * @param string $value
     *
     * @return string
     */
    private function extendValue(string $target, string $value): string
    {
        foreach(($this->extensions[$target] ?? []) as $extension)
        {
            $value = $extension($value);
        }
        return $value;
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
        $this->node()->item($this->extendValue('item', $item));
        return $this;
    }
}
