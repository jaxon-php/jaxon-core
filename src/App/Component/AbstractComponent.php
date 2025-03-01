<?php

namespace Jaxon\App\Component;

use Jaxon\Di\Container;
use Jaxon\Script\JxnCall;
use Jaxon\Plugin\Request\CallableClass\ComponentHelper;

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
    abstract public function _initComponent(Container $di, ComponentHelper $xHelper);

    /**
     * Get the js call factory.
     *
     * @param string $sClassName
     *
     * @return JxnCall
     */
    public function rq(string $sClassName = ''): JxnCall
    {
        return $this->helper()->rq($sClassName);
    }
}
