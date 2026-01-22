<?php

namespace Jaxon\App;

use Jaxon\App\Component\ComponentFactory;
use Jaxon\Di\Container;
use Stringable;

abstract class NodeComponent extends Component\BaseComponent
{
    use Component\AjaxResponseTrait;
    use Component\NodeResponseTrait;

    /**
     * @inheritDoc
     */
    final protected function initComponent(Container $di, ComponentFactory $xFactory): void
    {
        $this->setFactory($xFactory);
        $this->setAjaxResponse($di);
        $this->setNodeResponse($di);
        // Allow the user app to setup the component.
        $this->setupComponent();
    }

    /**
     * @return string|Stringable
     */
    public function html(): string|Stringable
    {
        return '';
    }

    /**
     * Called before rendering the component.
     *
     * @return void
     */
    protected function before(): void
    {}

    /**
     * Called after rendering the component.
     *
     * @return void
     */
    protected function after(): void
    {}

    /**
     * Set the attached DOM node content with the component HTML code.
     *
     * @return void
     */
    final public function render(): void
    {
        $this->before();
        $this->node()->html($this->helper()->extendValue('html', (string)$this->html()));
        $this->after();
    }

    /**
     * Clear the attached DOM node content.
     *
     * @return void
     */
    final public function clear(): void
    {
        $this->node()->clear();
    }

    /**
     * Show/hide the attached DOM node.
     *
     * @return void
     */
    final public function visible(bool $bVisible): void
    {
        $bVisible ? $this->node()->jq()->show() : $this->node()->jq()->hide();
    }
}
