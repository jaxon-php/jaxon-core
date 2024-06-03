<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\ComponentResponse;

use function get_class;

class Component extends AbstractCallable
{
    /**
     * @var ComponentResponse
     */
    protected $response = null;

    /**
     * @inheritDoc
     */
    final public function _initCallable(Container $di)
    {
        $sClassName = get_class($this);
        $this->xCallableClassHelper = new CallableClassHelper($di, $sClassName);
        // Each component must have its own reponse object.
        $this->response = $di->newComponentResponse($sClassName);
    }

    /**
     * @inheritDoc
     */
    final protected function _response(): AjaxResponse
    {
        return $this->response;
    }

    /**
     * Get the HTML code of the component.
     *
     * @return string
     */
    public function html(): string
    {
        return '';
    }

    /**
     * Update the attached DOM node content with the component HTML code.
     *
     * @return ComponentResponse
     */
    final public function show()
    {
        $this->response->html($this->html());
        return $this->response;
    }
}
