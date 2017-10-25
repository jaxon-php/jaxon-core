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

use Jaxon\Utils\Container;

trait Manager
{
    /**
     * Get the plugin manager
     *
     * @return object        The plugin manager
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
     * @return \Jaxon\Plugin\Response
     */
    public function plugin($sName)
    {
        return $this->getPluginManager()->getResponsePlugin($sName);
    }

    /**
     * Get the request manager
     *
     * @return object        The request manager
     */
    public function getRequestManager()
    {
        return Container::getInstance()->getRequestManager();
    }

    /**
     * Get the response manager
     *
     * @return object        The response manager
     */
    public function getResponseManager()
    {
        return Container::getInstance()->getResponseManager();
    }

    /**
     * Get the Global Response object
     *
     * @return object        The Global Response object
     */
    public function getResponse()
    {
        return Container::getInstance()->getResponse();
    }

    /**
     * Create a new Jaxon response object
     *
     * @return \Jaxon\Response\Response        The new Jaxon response object
     */
    public function newResponse()
    {
        return Container::getInstance()->newResponse();
    }
}
