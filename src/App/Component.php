<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Response\Response;

abstract class Component extends AbstractComponent
{
    /**
     * @var Response
     */
    protected $response = null;

    /**
     * @inheritDoc
     */
    public function _initCallable(Container $di, CallableClassHelper $xCallableClassHelper)
    {
        parent::_initCallable($di, $xCallableClassHelper);

        $this->response = $di->getResponse();
    }

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
     * @return Response
     */
    final public function render(): Response
    {
        $this->before();
        $this->node()->html($this->html());
        $this->after();

        return $this->response;
    }

    /**
     * Clear the attached DOM node content.
     *
     * @return Response
     */
    final public function clear(): Response
    {
        $this->node()->clear();

        return $this->response;
    }

    /**
     * Show/hide the attached DOM node.
     *
     * @return Response
     */
    final public function visible(bool $bVisible): Response
    {
        $bVisible ? $this->node()->jq()->show() : $this->node()->jq()->hide();

        return $this->response;
    }
}
