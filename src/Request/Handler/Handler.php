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

use Jaxon\Jaxon;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Request\Plugin\FileUpload;

use Exception;

class Handler
{
    use \Jaxon\Features\Config;
    use \Jaxon\Features\Translator;

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
     * @var \Jaxon\Plugin\Request
     */
    private $xTargetRequestPlugin = null;

    /**
     * The file upload request plugin
     *
     * @var FileUpload
     */
    private $xUploadRequestPlugin = null;

    /**
     * The constructor
     *
     * @param PluginManager         $xPluginManager
     * @param ResponseManager       $xResponseManager
     * @param FileUpload            $xUploadRequestPlugin
     */
    public function __construct(PluginManager $xPluginManager,
        ResponseManager $xResponseManager, FileUpload $xUploadRequestPlugin)
    {
        $this->xPluginManager = $xPluginManager;
        $this->xResponseManager = $xResponseManager;
        $this->xUploadRequestPlugin = $xUploadRequestPlugin;

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
     * This is the pre-request processing callback passed to the Jaxon library.
     *
     * @param  boolean  &$bEndRequest if set to true, the request processing is interrupted.
     *
     * @return Jaxon\Response\Response  the Jaxon response
     */
    public function onBefore(&$bEndRequest)
    {
        // Call the user defined callback
        if(($xCallback = $this->xCallbackManager->before()))
        {
            call_user_func_array($xCallback, [$this->xTargetRequestPlugin->getTarget(), &$bEndRequest]);
        }
    }

    /**
     * This is the post-request processing callback passed to the Jaxon library.
     *
     * @return Jaxon\Response\Response  the Jaxon response
     */
    public function onAfter($bEndRequest)
    {
        if(($xCallback = $this->xCallbackManager->after()))
        {
            call_user_func_array($xCallback, [$this->xTargetRequestPlugin->getTarget(), $bEndRequest]);
        }
    }

    /**
     * This callback is called whenever an invalid request is processed.
     *
     * @return Jaxon\Response\Response  the Jaxon response
     */
    public function onInvalid($sMessage)
    {
        if(($xCallback = $this->xCallbackManager->invalid()))
        {
            call_user_func_array($xCallback, [$sMessage]);
        }
    }

    /**
     * This callback is called whenever an invalid request is processed.
     *
     * @return Jaxon\Response\Response  the Jaxon response
     */
    public function onError(Exception $xException)
    {
        if(($xCallback = $this->xCallbackManager->error()))
        {
            call_user_func_array($xCallback, [$xException]);
        }
        else
        {
            throw $xException;
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

        // If no other plugin than the upload plugin can process the request,
        // then it is a HTTP (not ajax) upload request
        $this->xUploadRequestPlugin->noRequestPluginFound();
        return $this->xUploadRequestPlugin->canProcessRequest();
    }

    /**
     * Process the current request
     *
     * Calls each of the request plugins to request that they process the current request.
     * If any plugin processes the request, it will return true.
     *
     * @return boolean
     */
    public function processRequest()
    {
        // Check to see if headers have already been sent out, in which case we can't do our job
        if(headers_sent($filename, $linenumber))
        {
            echo $this->trans('errors.output.already-sent', [
                'location' => $filename . ':' . $linenumber
            ]), "\n", $this->trans('errors.output.advice');
            exit();
        }

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
            try
            {
                // Process uploaded files
                $this->xUploadRequestPlugin->processRequest();

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

                if($e instanceof \Jaxon\Exception\Error)
                {
                    $this->onInvalid($e->getMessage());
                }
                else
                {
                    $this->onError($e);
                }
            }
        }

        // Clean the processing buffer
        if(($this->getOption('core.process.clean')))
        {
            $er = error_reporting(0);
            while(ob_get_level() > 0)
            {
                ob_end_clean();
            }
            error_reporting($er);
        }

        if(($this->xTargetRequestPlugin))
        {
            // Handle after processing event
            $this->onAfter($bEndRequest);
        }

        // If the called function returned no response, take the the global response
        if(!$this->xResponseManager->getResponse())
        {
            $this->xResponseManager->append(jaxon()->getResponse());
        }

        $this->xResponseManager->printDebug();

        if(($this->getOption('core.response.send')))
        {
            $this->xResponseManager->sendOutput();

            if(($this->getOption('core.process.exit')))
            {
                exit();
            }
        }
    }
}
