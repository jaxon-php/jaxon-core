<?php

namespace Jaxon\Request\Factory;

use Jaxon\Jaxon;

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

class Factory
{
    /**
     * @var Jaxon
     */
    private $jaxon;

    /**
     * @var ParameterFactory
     */
    protected $xParameterFactory;

    /**
     * The constructor.
     *
     * @param Jaxon $jaxon
     * @param ParameterFactory $xParameterFactory
     */
    public function __construct(Jaxon $jaxon, ParameterFactory $xParameterFactory)
    {
        $this->jaxon = $jaxon;
        $this->xParameterFactory = $xParameterFactory;
    }

    /**
     * Get the ajax request factory.
     *
     * @param string $sClassName
     *
     * @return RequestFactory|null
     */
    public function request(string $sClassName = ''): ?RequestFactory
    {
        return $this->jaxon->di()->getRequestFactory($sClassName);
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

