<?php

/**
 * RequestHandler.php - Jaxon Request Handler
 *
 * This class processes an incoming jaxon request.
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

namespace Jaxon\Request\Handler;

use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\RequestHandlerInterface;
use Jaxon\Plugin\Response\Databag\DatabagPlugin;
use Jaxon\Response\Manager\ResponseManager;
use Exception;

class RequestHandler
{
    /**
     * The request plugin that is able to process the current request
     *
     * @var RequestHandlerInterface
     */
    private $xRequestPlugin = null;

    /**
     * The constructor
     *
     * @param Container $di
     * @param PluginManager $xPluginManager
     * @param ResponseManager $xResponseManager
     * @param CallbackManager $xCallbackManager
     * @param DatabagPlugin $xDatabagPlugin
     */
    public function __construct(private Container $di, private PluginManager $xPluginManager,
        private ResponseManager $xResponseManager, private CallbackManager $xCallbackManager,
        private DatabagPlugin $xDatabagPlugin)
    {}

    /**
     * Check if the current request can be processed
     *
     * Calls each of the request plugins and determines if the current request can be processed by one of them.
     *
     * @return bool
     */
    public function canProcessRequest(): bool
    {
        // Return true if the request plugin was already found
        if($this->xRequestPlugin !== null)
        {
            return true;
        }

        // The HTTP request
        $xRequest = $this->di->getRequest();

        // Find a plugin to process the request
        foreach($this->xPluginManager->getRequestHandlers() as $sClassName)
        {
            if($sClassName::canProcessRequest($xRequest))
            {
                $this->xRequestPlugin = $this->di->g($sClassName);
                $xTarget = $this->xRequestPlugin->setTarget($xRequest);
                $xTarget->setMethodArgs($this->di->getRequestArguments());
                return true;
            }
        }
        return false;
    }

    /**
     * Process the current request and handle errors and exceptions.
     *
     * @return void
     * @throws RequestException
     */
    private function _processRequest(): void
    {
        // Process the request
        if($this->xRequestPlugin !== null)
        {
            $this->xRequestPlugin->processRequest();
            // Process the databag
            $this->xDatabagPlugin->writeCommand();
        }
    }

    /**
     * Process the current request.
     *
     * @return void
     * @throws RequestException
     */
    public function processRequest(): void
    {
        // We need the library to have been bootstrapped.
        $this->di->getBootstrap()->onBoot();

        // Check if there is a plugin to process this request
        if(!$this->canProcessRequest())
        {
            return;
        }

        try
        {
            $bEndRequest = false;
            // Handle before processing event
            if($this->xRequestPlugin !== null)
            {
                $this->xCallbackManager->onBefore($this->xRequestPlugin->getTarget(), $bEndRequest);
            }
            if($bEndRequest)
            {
                return;
            }

            $this->_processRequest();

            // Handle after processing event
            if($this->xRequestPlugin !== null)
            {
                $this->xCallbackManager->onAfter($this->xRequestPlugin->getTarget(), $bEndRequest);
            }
        }
        // An exception was thrown while processing the request.
        // The request missed the corresponding handler function,
        // or an error occurred while attempting to execute the handler.
        catch(RequestException $e)
        {
            $this->xResponseManager->error($e->getMessage());
            $this->xCallbackManager->onInvalid($e);
        }
        catch(Exception $e)
        {
            $this->xResponseManager->error($e->getMessage());
            $this->xCallbackManager->onError($e);
        }

        // Print the debug messages
        $this->xResponseManager->printDebug();
    }
}
