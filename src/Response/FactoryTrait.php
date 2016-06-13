<?php

/**
 * FactoryTrait.php - Trait for Jaxon Response Factory
 *
 * Make functions to create Jaxon Response objects available to Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response;

use \Jaxon\Jaxon;

trait FactoryTrait
{
    /**
     * The global response instance
     *
     * @var \Jaxon\Response\Response
     */
    protected $response = null;

    /**
     * Set the global Jaxon response object
     *
     * @return void
     */
    public function setGlobalResponse()
    {
        $this->response = Jaxon::getGlobalResponse();
    }

    /**
     * Create a new Jaxon response object
     *
     * @return \Jaxon\Response\Response        The new Jaxon response object
     */
    public function newResponse()
    {
        return new Response();
    }
}
