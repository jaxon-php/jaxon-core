<?php

/**
 * Factory.php - Jaxon Request Factory
 *
 * Create Jaxon client side requests to a given controller.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-framework
 */

namespace Jaxon\Module\Factory;

use Jaxon\Module\Controller;

class Request
{
    /**
     * The controller this request factory is attached to
     *
     * @var Jaxon\Module\Controller
     */
    private $controller = null;

    /**
     * The reflection class of the controller
     *
     * @var ReflectionClass
     */
    // private $reflectionClass;

    /**
     * Create a new Factory instance.
     *
     * @return void
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        // $this->reflectionClass = new \ReflectionClass(get_class($controller));
    }

    /**
     * Generate the corresponding javascript code for a call to any method
     *
     * @return string
     */
    public function __call($sMethod, $aArguments)
    {
        // Check if the method exists in the controller, and is public
        /*if(!$this->reflectionClass->hasMethod($sMethod))
        {
            // Todo: throw an exception
        }
        if(!$this->reflectionClass->getMethod($sMethod)->isPublic())
        {
            // Todo: throw an exception
        }*/
        // Prepend the controller class name to the method name
        $sMethod = $this->controller->getJaxonClassName() . '.' . $sMethod;
        // Make the request
        return call_user_func_array('\Jaxon\Request\Factory::call', array_merge(array($sMethod), $aArguments));
    }
}
