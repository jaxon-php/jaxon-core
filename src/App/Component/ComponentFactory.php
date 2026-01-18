<?php

/**
 * ComponentHelper.php
 *
 * Provides helper functions to components.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Component;

use Jaxon\Di\ComponentContainer;
use Jaxon\Exception\SetupException;
use Jaxon\Request\Target;
use Jaxon\Script\Call\JxnCall;

use function trim;

class ComponentFactory
{
    /**
     * @var ComponentHelper|null
     */
    private ComponentHelper|null $xHelper = null;

    /**
     * @var Target|null
     */
    private Target|null $xTarget = null;

    /**
     * @var JxnCall
     */
    private JxnCall $xJxnCall;

    /**
     * @param ComponentContainer $cdi
     * @param string $sClassName
     */
    public function __construct(private ComponentContainer $cdi, private string $sClassName)
    {}

    /**
     * @return ComponentHelper
     */
    public function helper(): ComponentHelper
    {
        return $this->xHelper ??= $this->cdi->getComponentHelper($this->sClassName);
    }

    /**
     * @return Target
     */
    public function target(): Target
    {
        return $this->xTarget ??= $this->cdi->getComponentTarget($this->sClassName);
    }

    /**
     * @return JxnCall
     */
    private function jxnCall(): JxnCall
    {
        return $this->xJxnCall ??= $this->cdi->getComponentRequestFactory($this->sClassName);
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
    public function cl(string $sClassName): mixed
    {
        return $this->cdi->makeComponent(trim($sClassName));
    }

    /**
     * Get the js call factory.
     *
     * @param string $sClassName
     *
     * @return JxnCall
     */
    public function rq(string $sClassName = ''): JxnCall
    {
        return !($sClassName = trim($sClassName)) ? $this->jxnCall() :
            $this->cdi->getComponentRequestFactory($sClassName);
    }
}
