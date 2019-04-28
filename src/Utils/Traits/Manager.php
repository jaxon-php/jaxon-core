<?php

/**
 * Container.php - Trait for Utils classes
 *
 * Make functions of the utils classes available to Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Traits;

use Jaxon\DI\Container;

trait Manager
{
    /**
     * Get the plugin manager
     *
     * @return Jaxon\Plugin\Manager
     */
    public function getPluginManager()
    {
        return Container::getInstance()->getPluginManager();
    }

    /**
     * Get a registered response plugin
     *
     * @param string        $sName                The name of the plugin
     *
     * @return Jaxon\Plugin\Response
     */
    public function plugin($sName)
    {
        return $this->getPluginManager()->getResponsePlugin($sName);
    }

    /**
     * Get the request manager
     *
     * @return Jaxon\Request\Handler
     */
    public function getRequestHandler()
    {
        return Container::getInstance()->getRequestHandler();
    }

    /**
     * Get the response manager
     *
     * @return Jaxon\Response\Manager
     */
    public function getResponseManager()
    {
        return Container::getInstance()->getResponseManager();
    }

    /**
     * Get the Global Response object
     *
     * @return Jaxon\Response\Response
     */
    public function getResponse()
    {
        return Container::getInstance()->getResponse();
    }

    /**
     * Create a new Jaxon response object
     *
     * @return Jaxon\Response\Response
     */
    public function newResponse()
    {
        return Container::getInstance()->newResponse();
    }
}
