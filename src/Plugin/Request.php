<?php

/**
 * Request.php - Xajax Request interface
 *
 * Interface for Xajax Request plugins.
 *
 * Request plugins handle the registration, client script generation and processing of
 * xajax enabled requests.
 * Each plugin should have a unique signature for both the registration and processing
 * of requests.
 * During registration, the user will specify a type which will allow the plugin
 * to detect and handle it.
 * During client script generation, the plugin will generate a <xajax.request> stub with
 * the prescribed call options and request signature.
 * During request processing, the plugin will detect the signature generated previously
 * and process the request accordingly.
 *
 * @package xajax-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax\Plugin;

abstract class Request extends Plugin
{
    /**
     * Register a function, an event or an object.
     *
     * Called by the <Xajax\Plugin\Manager> when a user script when a function, event
     * or callable object is to be registered.
     * Additional plugins may support other registration types.
     *
     * @return mixed
     */
    abstract public function register($aArgs);

    /**
     * Generate a unique hash for this plugin
     *
     * @return string
     */
    abstract public function generateHash();

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
     * Called by the <Xajax\Plugin\Manager> when a request has been received to determine
     * if the request is destinated to this request plugin.
     *
     * @return boolean
     */
    abstract public function canProcessRequest();
    
    /**
     * Process the current request
     *
     * Called by the <Xajax\Plugin\Manager> when a request is being processed.
     * This will only occur when <Xajax> has determined that the current request
     * is a valid (registered) xajax enabled function via <xajax->canProcessRequest>.
     *
     * @return boolean
     */
    abstract public function processRequest();
}
