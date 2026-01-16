<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\ComponentHelper;
use Stringable;

abstract class NodeComponent extends Component\AbstractComponent
{
    use Component\HelperTrait;
    use Component\NodeResponseTrait;
    use Component\AjaxResponseTrait;
    use Component\ComponentTrait;

    /**
     * @inheritDoc
     */
    final protected function initComponent(Container $di, ComponentHelper $xHelper): void
    {
        $this->setHelper($xHelper);
        $this->setNodeResponse($di);
        $this->setAjaxResponse($di);
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
        $this->node()->html((string)$this->html());
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
