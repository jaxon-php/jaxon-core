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
    final protected function _init(Container $di, ComponentHelper $xHelper)
    {
        $this->setHelper($xHelper);
        $this->setNodeResponse($di);
        $this->setAjaxResponse($di);
    }

    /**
     * @return string|Stringable
     */
    abstract public function html(): string|Stringable;

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
     * @return void
     */
    final public function render()
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
    final public function clear()
    {
        $this->node()->clear();
    }

    /**
     * Show/hide the attached DOM node.
     *
     * @return void
     */
    final public function visible(bool $bVisible)
    {
        $bVisible ? $this->node()->jq()->show() : $this->node()->jq()->hide();
    }
}
