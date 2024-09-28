<?php

namespace Jaxon\App;

use Jaxon\Response\ComponentResponse;

abstract class Component extends AbstractCallable
{
    use ComponentTrait;

    /**
     * @return string
     */
    abstract public function html(): string;

    /**
     * Called before rendering the component.
     *
     * @return void
     */
    protected function before()
    {}

    /**
     * Called after rendering the component.
     *
     * @return void
     */
    protected function after()
    {}

    /**
     * Set the attached DOM node content with the component HTML code.
     *
     * @return ComponentResponse
     */
    final public function render(): ComponentResponse
    {
        $this->before();
        $this->response->html($this->html());
        $this->after();

        return $this->response;
    }

    /**
     * Clear the attached DOM node content.
     *
     * @return ComponentResponse
     */
    final public function clear(): ComponentResponse
    {
        $this->response->clear();

        return $this->response;
    }

    /**
     * Show/hide the attached DOM node.
     *
     * @return ComponentResponse
     */
    final public function visible(bool $bVisible): ComponentResponse
    {
        $bVisible ? $this->response->jq()->show() : $this->response->jq()->hide();

        return $this->response;
    }
}
