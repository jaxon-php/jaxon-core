<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Response\AjaxResponse;
use Stringable;

abstract class Component extends AbstractComponent
{
    /**
     * @var AjaxResponse
     */
    protected $response = null;

    /**
     * @inheritDoc
     */
    public function _initCallable(Container $di, CallableClassHelper $xHelper)
    {
        parent::_initCallable($di, $xHelper);

        $this->response = $di->getResponse();
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
     * @return AjaxResponse
     */
    final public function render(): AjaxResponse
    {
        $this->before();
        $this->node()->html((string)$this->html());
        $this->after();

        return $this->response;
    }

    /**
     * Clear the attached DOM node content.
     *
     * @return AjaxResponse
     */
    final public function clear(): AjaxResponse
    {
        $this->node()->clear();

        return $this->response;
    }

    /**
     * Show/hide the attached DOM node.
     *
     * @return AjaxResponse
     */
    final public function visible(bool $bVisible): AjaxResponse
    {
        $bVisible ? $this->node()->jq()->show() : $this->node()->jq()->hide();

        return $this->response;
    }
}
