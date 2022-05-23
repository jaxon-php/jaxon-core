<?php

namespace Jaxon\Request\Factory;

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
     * @var RequestFactory
     */
    protected $xRequestFactory;

    /**
     * @var ParameterFactory
     */
    protected $xParameterFactory;

    /**
     * The constructor.
     *
     * @param CallableRegistry $xCallableRegistry
     * @param RequestFactory $xRequestFactory
     * @param ParameterFactory $xParameterFactory
     */
    public function __construct(CallableRegistry $xCallableRegistry,
        RequestFactory $xRequestFactory, ParameterFactory $xParameterFactory)
    {
        $this->xCallableRegistry = $xCallableRegistry;
        $this->xRequestFactory = $xRequestFactory;
        $this->xParameterFactory = $xParameterFactory;
    }

    /**
     * Get the ajax request factory.
     *
     * @param string $sClassName
     *
     * @return RequestFactory|null
     * @throws SetupException
     */
    public function request(string $sClassName = ''): ?RequestFactory
    {
        $sClassName = trim($sClassName);
        if(!$sClassName)
        {
            // There is a single request factory for all callable functions.
            return $this->xRequestFactory->noPrefix(false);
        }
        if($sClassName === '.')
        {
            // The request factory is for a js function, not a Jaxon call.
            return $this->xRequestFactory->noPrefix(true);
        }
        // While each callable class has it own request factory.
        return $this->xCallableRegistry->getRequestFactory($sClassName);
    }

    /**
     * Get the request parameter factory.
     *
     * @return ParameterFactory
     */
    public function parameter(): ParameterFactory
    {
        return $this->xParameterFactory;
    }
}
