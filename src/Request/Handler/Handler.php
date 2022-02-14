<?php

/**
 * Handler.php - Jaxon Request Handler
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

use Jaxon\Exception\Error;
use Jaxon\Jaxon;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Plugin\Request;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Request\Plugin\FileUpload;
use Jaxon\Response\Plugin\DataBag;
use Jaxon\Response\AbstractResponse;

use Exception;

class Handler
{
    use \Jaxon\Features\Config;

    /**
     * The plugin manager.
     *
     * @var PluginManager
     */
    private $xPluginManager;

    /**
     * The response manager.
     *
     * @var ResponseManager
     */
    private $xResponseManager;

    /**
     * The arguments handler.
     *
     * @var Argument
     */
    private $xArgumentManager;

    /**
     * The callbacks to run while processing the request
     *
     * @var Callback
     */
    private $xCallbackManager;

    /**
     * The request plugin that is able to process the current request
     *
     * @var Request
     */
    private $xTargetRequestPlugin = null;

    /**
     * The file upload request plugin
     *
     * @var FileUpload
     */
    private $xUploadPlugin = null;

    /**
     * The data bag response plugin
     *
     * @var DataBag
     */
    private $xDataBagPlugin = null;

    /**
     * The constructor
     *
     * @param PluginManager         $xPluginManager
     * @param ResponseManager       $xResponseManager
     * @param FileUpload            $xUploadPlugin
     * @param DataBag               $xDataBagPlugin
     */
    public function __construct(PluginManager $xPluginManager,
        ResponseManager $xResponseManager, FileUpload $xUploadPlugin, DataBag $xDataBagPlugin)
    {
        $this->xPluginManager = $xPluginManager;
        $this->xResponseManager = $xResponseManager;
        $this->xUploadPlugin = $xUploadPlugin;
        $this->xDataBagPlugin = $xDataBagPlugin;

        $this->xArgumentManager = new Argument();
        $this->xCallbackManager = new Callback();
    }

    /**
     * Return the method that was used to send the arguments from the client
     *
     * The method is one of: Argument::METHOD_UNKNOWN, Argument::METHOD_GET, Argument::METHOD_POST.
     *
     * @return integer
     */
    public function getRequestMethod()
    {
        return $this->xArgumentManager->getRequestMethod();
    }

    /**
     * Return true if the current request method is GET
     *
     * @return bool
     */
    public function requestMethodIsGet()
    {
        return ($this->xArgumentManager->getRequestMethod() == Argument::METHOD_GET);
    }

    /**
     * Return the array of arguments that were extracted and parsed from the GET or POST data
     *
     * @return array
     * @throws Error
     */
    public function processArguments()
    {
        return $this->xArgumentManager->process();
    }

    /**
     * Get the callback handler
     *
     * @return Callback
     */
    public function getCallbackManager()
    {
        return $this->xCallbackManager;
    }

    /**
     * These callbacks are called whenever an invalid request is processed.
     *
     * @return void
     */
    public function onBoot()
    {
        foreach($this->xCallbackManager->getBootCallbacks() as $xCallback)
        {
            call_user_func($xCallback);
        }
    }

    /**
     * These are the pre-request processing callbacks passed to the Jaxon library.
     *
     * @param  boolean  $bEndRequest   If set to true, the request processing is interrupted.
     *
     * @return void
     */
    public function onBefore(&$bEndRequest)
    {
        // Call the user defined callback
        foreach($this->xCallbackManager->getBeforeCallbacks() as $xCallback)
        {
            $xReturn = call_user_func_array($xCallback,
                [$this->xTargetRequestPlugin->getTarget(), &$bEndRequest]);
            if($xReturn instanceof AbstractResponse)
            {
                $this->xResponseManager->append($xReturn);
            }
        }
    }

    /**
     * These are the post-request processing callbacks passed to the Jaxon library.
     *
     * @return void
     */
    public function onAfter($bEndRequest)
    {
        foreach($this->xCallbackManager->getAfterCallbacks() as $xCallback)
        {
            $xReturn = call_user_func_array($xCallback,
                [$this->xTargetRequestPlugin->getTarget(), $bEndRequest]);
            if($xReturn instanceof AbstractResponse)
            {
                $this->xResponseManager->append($xReturn);
            }
        }
    }

    /**
     * These callbacks are called whenever an invalid request is processed.
     *
     * @return void
     */
    public function onInvalid($sMessage)
    {
        foreach($this->xCallbackManager->getInvalidCallbacks() as $xCallback)
        {
            $xReturn = call_user_func($xCallback, $sMessage);
            if($xReturn instanceof AbstractResponse)
            {
                $this->xResponseManager->append($xReturn);
            }
        }
    }

    /**
     * These callbacks are called whenever an invalid request is processed.
     *
     * @return void
     * @throws Exception
     */
    public function onError(Exception $xException)
    {
        foreach($this->xCallbackManager->getErrorCallbacks() as $xCallback)
        {
            $xReturn = call_user_func($xCallback, $xException);
            if($xReturn instanceof AbstractResponse)
            {
                $this->xResponseManager->append($xReturn);
            }
        }
    }

    /**
     * Check if the current request can be processed
     *
     * Calls each of the request plugins and determines if the current request can be processed by one of them.
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        // Return true if the request plugin was already found
        if(($this->xTargetRequestPlugin))
        {
            return true;
        }

        // Find a plugin to process the request
        foreach($this->xPluginManager->getRequestPlugins() as $xPlugin)
        {
            if($xPlugin->getName() != Jaxon::FILE_UPLOAD && $xPlugin->canProcessRequest())
            {
                $this->xTargetRequestPlugin = $xPlugin;
                return true;
            }
        }

        // Check if the upload plugin is enabled
        if(!$this->getOption('core.upload.enabled'))
        {
            return false;
        }

        // If no other plugin than the upload plugin can process the request,
        // then it is a HTTP (not ajax) upload request
        $this->xUploadPlugin->noRequestPluginFound();
        return $this->xUploadPlugin->canProcessRequest();
    }

    /**
     * Process the current request and handle errors and exceptions.
     *
     * @return void
     */
    private function _processRequest()
    {
        try
        {
            // Process uploaded files, if the upload plugin is enabled
            if($this->getOption('core.upload.enabled'))
            {
                $this->xUploadPlugin->processRequest();
            }

            // Process the request
            if(($this->xTargetRequestPlugin))
            {
                $this->xTargetRequestPlugin->processRequest();
            }
        }
        catch(Exception $e)
        {
            // An exception was thrown while processing the request.
            // The request missed the corresponding handler function,
            // or an error occurred while attempting to execute the handler.

            $this->xResponseManager->error($e->getMessage());

            if($e instanceof Error)
            {
                $this->onInvalid($e->getMessage());
            }
            else
            {
                $this->onError($e);
            }
        }
    }

    /**
     * Clean output buffers.
     *
     * @return void
     */
    private function _cleanOutputBuffers()
    {
        $er = error_reporting(0);
        while(ob_get_level() > 0)
        {
            ob_end_clean();
        }
        error_reporting($er);
    }

    /**
     * Process the current request.
     *
     * Calls each of the request plugins to request that they process the current request.
     * If any plugin processes the request, it will return true.
     *
     * @return void
     */
    public function processRequest()
    {
        // Check if there is a plugin to process this request
        if(!$this->canProcessRequest())
        {
            return;
        }

        $bEndRequest = false;

        // Handle before processing event
        if(($this->xTargetRequestPlugin))
        {
            $this->onBefore($bEndRequest);
        }

        if(!$bEndRequest)
        {
            $this->_processRequest();
            // Process the databag
            $this->xDataBagPlugin->writeCommand();
        }

        // Clean the processing buffer
        if(($this->getOption('core.process.clean')))
        {
            $this->_cleanOutputBuffers();
        }

        if(($this->xTargetRequestPlugin))
        {
            // Handle after processing event
            $this->onAfter($bEndRequest);
        }

        // If the called function returned no response, take the global response
        if(!$this->xResponseManager->getResponse())
        {
            $this->xResponseManager->append(jaxon()->getResponse());
        }

        $this->xResponseManager->printDebug();
    }
}
