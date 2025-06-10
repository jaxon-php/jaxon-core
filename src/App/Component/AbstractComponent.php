<?php

namespace Jaxon\App\Component;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\ComponentHelper;
use Jaxon\Script\Call\JxnCall;

abstract class AbstractComponent
{
    /**
     * Get the component helper
     *
     * @return ComponentHelper
     */
    abstract protected function helper(): ComponentHelper;

    /**
     * Initialize the component
     *
     * @param Container $di
     * @param ComponentHelper $xHelper
     *
     * @return void
     */
    abstract protected function _init(Container $di, ComponentHelper $xHelper);

    /**
     * Get the js call factory.
     *
     * @param string $sClassName
     *
     * @return JxnCall
     */
    protected function rq(string $sClassName = ''): JxnCall
    {
        return $this->helper()->rq($sClassName);
    }
}
