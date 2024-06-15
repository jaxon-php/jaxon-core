<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\ComponentResponse;

use function get_class;

abstract class Component extends AbstractCallable
{
    /**
     * @var ComponentResponse
     */
    protected $response = null;

    /**
     * @var string
     */
    protected $overrides = '';

    /**
     * @inheritDoc
     */
    public function _initCallable(Container $di)
    {
        $sClassName = get_class($this);
        $this->xCallableClassHelper = new CallableClassHelper($di, $sClassName);

        // Each component must have its own reponse object.
        // A component can overrides another one. In this case,
        // its response is attached to the overriden DOM node.
        $this->response = $di->newComponentResponse($this->overrides ?: $sClassName);
    }

    /**
     * @inheritDoc
     */
    final protected function _response(): AjaxResponse
    {
        return $this->response;
    }

    /**
     * Set the attached DOM node content with the component HTML code.
     *
     * @return ComponentResponse
     */
    final public function refresh(): ComponentResponse
    {
        $this->response->html($this->html());
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
     * Show the attached DOM node.
     *
     * @return ComponentResponse
     */
    final public function show(): ComponentResponse
    {
        $this->jq()->show();
        return $this->response;
    }

    /**
     * Hide the attached DOM node.
     *
     * @return ComponentResponse
     */
    final public function hide(): ComponentResponse
    {
        $this->jq()->hide();
        return $this->response;
    }
}
