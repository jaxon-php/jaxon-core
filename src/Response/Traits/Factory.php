<?php

/**
 * Factory.php - Trait for Jaxon Response Factory
 *
 * Make functions to create Jaxon Response objects available to Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response\Traits;

use \Jaxon\Jaxon;

trait Factory
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
        $this->response = jaxon()->getResponse();
    }

    /**
     * Create a new Jaxon response object
     *
     * @return \Jaxon\Response\Response        The new Jaxon response object
     */
    public function newResponse()
    {
        return jaxon()->newResponse();
    }
}
