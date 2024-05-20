<?php

namespace Jaxon\JsCall;

/**
 * Factory.php
 *
 * Gives access to the factories.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Dialog\DialogManager;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\CallableRegistry;

use function trim;

class Factory
{
    /**
     * @var CallableRegistry
     */
    private $xCallableRegistry;

    /**
     * @var DialogManager
     */
    protected $xDialogManager;

    /**
     * @var ParameterFactory
     */
    protected $xParameterFactory;

    /**
     * @var CallFactory
     */
    protected $xRqFunctionFactory;

    /**
     * @var CallFactory
     */
    protected $xJsFunctionFactory;

    /**
     * The constructor.
     *
     * @param CallableRegistry $xCallableRegistry
     * @param DialogManager $xDialogManager
     * @param ParameterFactory $xParameterFactory
     * @param string $sFunctionPrefix
     */
    public function __construct(CallableRegistry $xCallableRegistry,
        DialogManager $xDialogManager, ParameterFactory $xParameterFactory, string $sFunctionPrefix)
    {
        $this->xCallableRegistry = $xCallableRegistry;
        $this->xDialogManager = $xDialogManager;
        $this->xParameterFactory = $xParameterFactory;
        // Factory for registered functions
        $this->xRqFunctionFactory = new CallFactory($sFunctionPrefix, $this->xDialogManager);
        // Factory for Js functions
        $this->xJsFunctionFactory = new CallFactory('', $this->xDialogManager);
    }

    /**
     * Get the js call factory.
     *
     * @param string $sClassName
     *
     * @return CallFactory|null
     * @throws SetupException
     */
    public function rq(string $sClassName = ''): ?CallFactory
    {
        $sClassName = trim($sClassName);
        // There is a single request factory for all callable functions,
        // while each callable class has it own request factory.
        return !$sClassName ? $this->xRqFunctionFactory :
            $this->xCallableRegistry->getCallFactory($sClassName);
    }

    /**
     * Get the js call factory.
     *
     * @param string $sClassName
     *
     * @return CallFactory|null
     */
    public function js(string $sClassName = ''): ?CallFactory
    {
        $sClassName = trim($sClassName);
        // There is a single request factory for all js functions,
        // while each js object has it own request factory.
        return !$sClassName ? $this->xJsFunctionFactory :
            new CallFactory($sClassName . '.', $this->xDialogManager);
    }

    /**
     * Get the js call parameter factory.
     *
     * @return ParameterFactory
     */
    public function pm(): ParameterFactory
    {
        return $this->xParameterFactory;
    }
}
