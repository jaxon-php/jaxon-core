<?php

/**
 * RequestHandler.php - Jaxon RequestPlugin Handler
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

use Jaxon\Config\ConfigManager;
use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Contract\RequestHandlerInterface;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Response\AbstractResponse;
use Jaxon\Response\Plugin\DataBag\DataBagPlugin;
use Jaxon\Response\ResponseManager;
use Jaxon\Utils\Translation\Translator;
use Psr\Http\Message\ServerRequestInterface;

use Exception;

use function call_user_func;
use function call_user_func_array;
use function error_reporting;
use function headers_sent;
use function ob_end_clean;
use function ob_get_level;

class RequestHandler
{
    /**
     * @var Container
     */
    private $di;

    /**
     * @var ConfigManager
     */
    protected $xConfigManager;

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
     * The argument manager.
     *
     * @var ArgumentManager
     */
    private $xArgumentManager;

    /**
     * The callbacks to run while processing the request
     *
     * @var CallbackManager
     */
    private $xCallbackManager;

    /**
     * @var UploadHandler
     */
    private $xUploadHandler;

    /**
     * The data bag response plugin
     *
     * @var DataBagPlugin
     */
    private $xDataBagPlugin;

    /**
     * @var Translator
     */
    private $xTranslator;

    /**
     * The request plugin that is able to process the current request
     *
     * @var RequestHandlerInterface
     */
    private $xRequestPlugin = null;

    /**
     * @var ServerRequestInterface
     */
    private $xRequest;

    /**
     * The constructor
     *
     * @param Container $di
     * @param ConfigManager $xConfigManager
     * @param ArgumentManager $xArgumentManager
     * @param PluginManager $xPluginManager
     * @param ResponseManager $xResponseManager
     * @param CallbackManager $xCallbackManager
     * @param ServerRequestInterface $xRequest
     * @param UploadHandler|null $xUploadHandler
     * @param DataBagPlugin $xDataBagPlugin
     * @param Translator $xTranslator
     */
    public function __construct(Container $di, ConfigManager $xConfigManager, ArgumentManager $xArgumentManager,
        PluginManager $xPluginManager, ResponseManager $xResponseManager, CallbackManager $xCallbackManager,
        ServerRequestInterface $xRequest, ?UploadHandler $xUploadHandler, DataBagPlugin $xDataBagPlugin,
        Translator $xTranslator)
    {
        $this->di = $di;
        $this->xConfigManager = $xConfigManager;
        $this->xPluginManager = $xPluginManager;
        $this->xResponseManager = $xResponseManager;
        $this->xArgumentManager = $xArgumentManager;
        $this->xCallbackManager = $xCallbackManager;
        $this->xRequest = $xRequest;
        $this->xUploadHandler = $xUploadHandler;
        $this->xDataBagPlugin = $xDataBagPlugin;
        $this->xTranslator = $xTranslator;
    }

    /**
     * Return the array of arguments that were extracted and parsed from the GET or POST data
     *
     * @return array
     * @throws RequestException
     */
    public function processArguments(): array
    {
        return $this->xArgumentManager->process();
    }

    /**
     * Get the callback handler
     *
     * @return CallbackManager
     */
    public function getCallbackManager(): CallbackManager
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
     * @param bool $bEndRequest    If set to true, the request processing is interrupted.
     *
     * @return void
     */
    public function onBefore(bool &$bEndRequest)
    {
        $xTarget = $this->xRequestPlugin->getTarget();
        // Call the user defined callback
        foreach($this->xCallbackManager->getBeforeCallbacks() as $xCallback)
        {
            $xReturn = call_user_func_array($xCallback, [$xTarget, &$bEndRequest]);
            if($bEndRequest)
            {
                return;
            }
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
    public function onAfter(bool $bEndRequest)
    {
        foreach($this->xCallbackManager->getAfterCallbacks() as $xCallback)
        {
            $xReturn = call_user_func_array($xCallback,
                [$this->xRequestPlugin->getTarget(), $bEndRequest]);
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
    public function onInvalid(string $sMessage)
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
     * @var Exception $xException
     *
     * @return void
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
     * @return bool
     */
    public function canProcessRequest(): bool
    {
        // Return true if the request plugin was already found
        if($this->xRequestPlugin !== null)
        {
            return true;
        }

        // Find a plugin to process the request
        foreach($this->xPluginManager->getRequestHandlers() as $sClassName)
        {
            if($sClassName::canProcessRequest($this->xRequest))
            {
                $this->xRequestPlugin = $this->di->g($sClassName);
                return true;
            }
        }

        // Check if the upload plugin is enabled
        if($this->xUploadHandler === null)
        {
            return false;
        }

        // If no other plugin than the upload plugin can process the request,
        // then it is an HTTP (not ajax) upload request
        $this->xUploadHandler->isHttpUpload();
        return $this->xUploadHandler->canProcessRequest();
    }

    /**
     * Process the current request and handle errors and exceptions.
     *
     * @return void
     * @throws RequestException
     * @throws SetupException
     */
    private function _processRequest()
    {
        try
        {
            $bEndRequest = false;
            // Handle before processing event
            if(($this->xRequestPlugin))
            {
                $this->onBefore($bEndRequest);
            }
            if($bEndRequest)
            {
                return;
            }

            // Process uploaded files, if the upload plugin is enabled
            if($this->xUploadHandler !== null)
            {
                $this->xUploadHandler->processRequest();
            }
            // Process the request
            if(($this->xRequestPlugin))
            {
                $this->xRequestPlugin->processRequest();
            }

            // Handle after processing event
            if(($this->xRequestPlugin))
            {
                $this->onAfter($bEndRequest);
            }
        }
        catch(Exception $e)
        {
            // An exception was thrown while processing the request.
            // The request missed the corresponding handler function,
            // or an error occurred while attempting to execute the handler.
            $this->xResponseManager->error($e->getMessage());
            if($e instanceof RequestException || $e instanceof SetupException)
            {
                $this->onInvalid($e->getMessage());
            }
            else
            {
                $this->onError($e);
            }
            throw $e;
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
     * @throws RequestException
     * @throws SetupException
     */
    public function processRequest()
    {
        // Check to see if headers have already been sent out, in which case we can't do our job
        if(headers_sent($sFilename, $nLineNumber))
        {
            echo $this->xTranslator->trans('errors.output.already-sent', [
                'location' => $sFilename . ':' . $nLineNumber
            ]), "\n", $this->xTranslator->trans('errors.output.advice');
            exit();
        }

        // Check if there is a plugin to process this request
        if(!$this->canProcessRequest())
        {
            return;
        }

        $this->_processRequest();

        // Process the databag
        $this->xDataBagPlugin->writeCommand();

        // Clean the processing buffer
        if(($this->xConfigManager->getOption('core.process.clean')))
        {
            $this->_cleanOutputBuffers();
        }

        // If the called function returned no response, take the global response
        if(!$this->xResponseManager->getResponse())
        {
            $this->xResponseManager->append($this->di->getResponse());
        }

        $this->xResponseManager->printDebug();

        if(($this->xConfigManager->getOption('core.response.send')))
        {
            $this->xResponseManager->sendOutput();
            if(($this->xConfigManager->getOption('core.process.exit')))
            {
                exit();
            }
        }
    }
}
