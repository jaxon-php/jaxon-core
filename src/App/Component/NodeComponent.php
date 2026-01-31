<?php

namespace Jaxon\App\Component;

use Jaxon\Di\Container;
use Stringable;

abstract class NodeComponent extends BaseComponent
{
    use AjaxResponseTrait;
    use NodeResponseTrait;

    /**
     * @inheritDoc
     */
    final protected function initComponent(Container $di, ComponentFactory $xFactory): void
    {
        $this->setFactory($xFactory);
        $this->setAjaxResponse($di);
        $this->setNodeResponse($di);
        // Allow the user app to customize the component setup.
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
}
