<?php

/**
 * Request.php - Jaxon Request interface
 *
 * Interface for Jaxon Request plugins.
 *
 * Request plugins handle the registration, client script generation and processing of jaxon enabled requests.
 * Each plugin should have a unique signature for both the registration and processing of requests.
 * During registration, the user will specify a type which will allow the plugin to detect and handle it.
 * During client script generation, the plugin will generate a <jaxon.request> stub with the prescribed call options and request signature.
 * During request processing, the plugin will detect the signature generated previously and process the request accordingly.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin;

abstract class Request extends Plugin
{
    /**
     * Register a function, an event or an object.
     *
     * Called by the <Jaxon\Plugin\Manager> when a user script when a function, event
     * or callable object is to be registered.
     * Additional plugins may support other registration types.
     *
     * @return mixed
     */
    abstract public function register($aArgs);

    /**
     * Return true if the object is a request plugin. Always return true here.
     *
     * @return boolean
     */
    public function isRequest()
    {
        return true;
    }

    /**
     * Check if this plugin can process the current request
     *
     * Called by the <Jaxon\Plugin\Manager> when a request has been received to determine
     * if the request is destinated to this request plugin.
     *
     * @return boolean
     */
    abstract public function canProcessRequest();
    
    /**
     * Process the current request
     *
     * Called by the <Jaxon\Plugin\Manager> when a request is being processed.
     * This will only occur when <Jaxon> has determined that the current request
     * is a valid (registered) jaxon enabled function via <jaxon->canProcessRequest>.
     *
     * @return boolean
     */
    abstract public function processRequest();
}
