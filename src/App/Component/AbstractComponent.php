<?php

namespace Jaxon\App\Component;

use Jaxon\App\Component\ComponentFactory;
use Jaxon\Exception\SetupException;
use Jaxon\Di\Container;
use Jaxon\Script\Call\JxnCall;

abstract class AbstractComponent
{
    /**
     * @var ComponentFactory
     */
    protected readonly ComponentFactory $xFactory;

    /**
     * @param ComponentFactory $xFactory
     *
     * @return void
     */
    protected function setFactory(ComponentFactory $xFactory): void
    {
        $this->xFactory = $xFactory;
    }

    /**
     * @return ComponentFactory
     */
    protected function factory(): ComponentFactory
    {
        return $this->xFactory;
    }

    /**
     * Initialize the component
     *
     * @param Container $di
     * @param ComponentFactory $xFactory
     *
     * @return void
     */
    abstract protected function initComponent(Container $di, ComponentFactory $xFactory);

    /**
     * Get the js call factory.
     *
     * @param string $sClassName
     *
     * @return JxnCall
     */
    protected function rq(string $sClassName = ''): JxnCall
    {
        return $this->factory()->rq($sClassName);
    }

    /**
     * Get an instance of a Jaxon class by name
     *
     * @template T
     * @param class-string<T> $sClassName the class name
     *
     * @return T|null
     * @throws SetupException
     */
    protected function cl(string $sClassName): mixed
    {
        return $this->factory()->cl($sClassName);
    }
}
