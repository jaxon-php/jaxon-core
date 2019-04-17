<?php

/**
 * Jaxon.php - Jaxon class
 *
 * The Jaxon class uses a modular plug-in system to facilitate the processing
 * of special Ajax requests made by a PHP page.
 * It generates Javascript that the page must include in order to make requests.
 * It handles the output of response commands (see <Jaxon\Response\Response>).
 * Many flags and settings can be adjusted to effect the behavior of the Jaxon class
 * as well as the client-side javascript.
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

namespace Jaxon;

use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Request\Manager as RequestManager;
use Jaxon\Response\Manager as ResponseManager;

use Jaxon\Utils\URI;
use Jaxon\Utils\Container;
use Exception;
use Closure;

class Jaxon
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Translator;
    use \Jaxon\Utils\Traits\Paginator;
    use \Jaxon\Utils\Traits\Template;

    use Traits\Autoload;
    use Traits\Config;
    use Traits\Plugin;
    use Traits\Upload;
    use Traits\Sentry;

    /**
     * Package version number
     *
     * @var string
     */
    private $sVersion = 'Jaxon 2.2.6';

    /*
     * Processing events
     */
    const PROCESSING_EVENT = 'ProcessingEvent';
    const PROCESSING_EVENT_BEFORE = 'BeforeProcessing';
    const PROCESSING_EVENT_AFTER = 'AfterProcessing';
    const PROCESSING_EVENT_INVALID = 'InvalidRequest';
    const PROCESSING_EVENT_ERROR = 'ProcessingError';

    /*
     * Request methods
     */
    const METHOD_UNKNOWN = 0;
    const METHOD_GET = 1;
    const METHOD_POST = 2;

    /*
     * Request plugins
     */
    // For objects who's methods will be callable from the browser.
    const CALLABLE_OBJECT = 'CallableObject';
    // For functions available at global scope, or from an instance of an object.
    const USER_FUNCTION = 'UserFunction';
    // For browser events.
    const BROWSER_EVENT = 'BrowserEvent';
    // For event handlers.
    const EVENT_HANDLER = 'EventHandler';
    // For uploaded files.
    const FILE_UPLOAD = 'FileUpload';

    /*
     * Request parameters
     */
    // Specifies that the parameter will consist of an array of form values.
    const FORM_VALUES = 'FormValues';
    // Specifies that the parameter will contain the value of an input control.
    const INPUT_VALUE = 'InputValue';
    // Specifies that the parameter will consist of a boolean value of a checkbox.
    const CHECKED_VALUE = 'CheckedValue';
    // Specifies that the parameter value will be the innerHTML value of the element.
    const ELEMENT_INNERHTML = 'ElementInnerHTML';
    // Specifies that the parameter will be a quoted value (string).
    const QUOTED_VALUE = 'QuotedValue';
    // Specifies that the parameter will be a boolean value (true or false).
    const BOOL_VALUE = 'BoolValue';
    // Specifies that the parameter will be a numeric, non-quoted value.
    const NUMERIC_VALUE = 'NumericValue';
    // Specifies that the parameter will be a non-quoted value
    // (evaluated by the browsers javascript engine at run time).
    const JS_VALUE = 'UnquotedValue';
    // Specifies that the parameter will be an integer used to generate pagination links.
    const PAGE_NUMBER = 'PageNumber';

    /**
     * Processing event handlers that have been assigned during this run of the script
     *
     * @var array
     */
    private $aProcessingEvents;

    public function __construct()
    {
        $this->aProcessingEvents = array();
        $this->setDefaultOptions();
    }

    /**
     * The current Jaxon version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->sVersion;
    }

    /**
     * Register request handlers, including functions, callable objects and events.
     *
     * New plugins can be added that support additional registration methods and request processors.
     *
     * @param string    $sType            The type of request handler being registered
     *        Options include:
     *        - Jaxon::USER_FUNCTION: a function declared at global scope
     *        - Jaxon::CALLABLE_OBJECT: an object who's methods are to be registered
     *        - Jaxon::BROWSER_EVENT: an event which will cause zero or more event handlers to be called
     *        - Jaxon::EVENT_HANDLER: register an event handler function.
     * @param mixed        $sFunction | $objObject | $sEvent
     *        When registering a function, this is the name of the function
     *        When registering a callable object, this is the object being registered
     *        When registering an event or event handler, this is the name of the event
     * @param mixed        $sIncludeFile | $aCallOptions | $sEventHandler
     *        When registering a function, this is the (optional) include file
     *        When registering a callable object, this is an (optional) array
     *             of call options for the functions being registered
     *        When registering an event handler, this is the name of the function
     *
     * @return mixed
     */
    public function register($sType, $xArgs)
    {
        $aArgs = func_get_args();
        $nArgs = func_num_args();

        if(self::PROCESSING_EVENT == $sType)
        {
            if($nArgs > 2)
            {
                $sEvent = $xArgs;
                $xUserFunction = $aArgs[2];
                if(!is_a($xUserFunction, 'Request\\Support\\UserFunction'))
                    $xUserFunction = new Request\Support\UserFunction($xUserFunction);
                $this->aProcessingEvents[$sEvent] = $xUserFunction;
            }
            /*else
            {
                // Todo: return error
            }*/
            return true;
        }

        return $this->getPluginManager()->register($aArgs);
    }

    /**
     * Add a path to the class directories
     *
     * @param string            $sDirectory             The path to the directory
     * @param string|null       $sNamespace             The associated namespace
     * @param string            $sSeparator             The character to use as separator in javascript class names
     * @param array             $aProtected             The functions that are not to be exported
     *
     * @return boolean
     */
    public function addClassDir($sDirectory, $sNamespace = null, $sSeparator = '.', array $aProtected = array())
    {
        return $this->getPluginManager()->addClassDir($sDirectory, $sNamespace, $sSeparator, $aProtected);
    }

    /**
     * Register callable objects from all class directories
     *
     * @param array             $aOptions               The options to register the classes with
     *
     * @return void
     */
    public function registerClasses(array $aOptions = array())
    {
        return $this->getPluginManager()->registerClasses($aOptions);
    }

    /**
     * Register a callable object from one of the class directories
     *
     * The class name can be dot, slash or anti-slash separated.
     * If the $bGetObject parameter is set to true, the registered instance of the class is returned.
     *
     * @param string            $sClassName             The name of the class to register
     * @param array             $aOptions               The options to register the class with
     * @param boolean           $bGetObject             Return the registered instance of the class
     *
     * @return void
     */
    public function registerClass($sClassName, array $aOptions = array(), $bGetObject = false)
    {
        $this->getPluginManager()->registerClass($sClassName, $aOptions);
        return (($bGetObject) ? $this->getPluginManager()->getRegisteredObject($sClassName) : null);
    }

    /**
     * Returns the Jaxon Javascript header and wrapper code to be printed into the page
     *
     * The javascript code returned by this function is dependent on the plugins
     * that are included and the functions and classes that are registered.
     *
     * @param boolean        $bIncludeJs            Also get the JS files
     * @param boolean        $bIncludeCss        Also get the CSS files
     *
     * @return string
     */
    public function getScript($bIncludeJs = false, $bIncludeCss = false)
    {
        if(!$this->getOption('core.request.uri'))
        {
            $this->setOption('core.request.uri', URI::detect());
        }
        $sCode = '';
        if(($bIncludeCss))
        {
            $sCode .= $this->getPluginManager()->getCss() . "\n";
        }
        if(($bIncludeJs))
        {
            $sCode .= $this->getPluginManager()->getJs() . "\n";
        }
        $sCode .= $this->getPluginManager()->getScript();
        return $sCode;
    }

    /**
     * Print the jaxon Javascript header and wrapper code into your page
     *
     * The javascript code returned by this function is dependent on the plugins
     * that are included and the functions and classes that are registered.
     *
     * @param boolean        $bIncludeJs            Also print the JS files
     * @param boolean        $bIncludeCss        Also print the CSS files
     *
     * @return void
     */
    public function printScript($bIncludeJs = false, $bIncludeCss = false)
    {
        print $this->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Return the javascript header code and file includes
     *
     * @return string
     */
    public function getJs()
    {
        return $this->getPluginManager()->getJs();
    }

    /**
     * Return the CSS header code and file includes
     *
     * @return string
     */
    public function getCss()
    {
        return $this->getPluginManager()->getCss();
    }

    /**
     * Determine if a call is a jaxon request or a page load request
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        return $this->getPluginManager()->canProcessRequest();
    }

    /**
     * If this is a jaxon request, call the requested PHP function, build the response and send it back to the browser
     *
     * This is the main server side engine for Jaxon.
     * It handles all the incoming requests, including the firing of events and handling of the response.
     * If your RequestURI is the same as your web page, then this function should be called before ANY
     * headers or HTML is output from your script.
     *
     * This function may exit after the request is processed, if the 'core.process.exit' option is set to true.
     *
     * @return void
     *
     * @see <Jaxon\Jaxon->canProcessRequest>
     */
    public function processRequest()
    {
        // Check to see if headers have already been sent out, in which case we can't do our job
        if(headers_sent($filename, $linenumber))
        {
            echo $this->trans('errors.output.already-sent', array(
                'location' => $filename . ':' . $linenumber
            )), "\n", $this->trans('errors.output.advice');
            exit();
        }

        // Check if there is a plugin to process this request
        if(!$this->canProcessRequest())
        {
            return;
        }

        $bEndRequest = false;
        $mResult = true;
        $xResponseManager = $this->getResponseManager();

        // Handle before processing event
        if(isset($this->aProcessingEvents[self::PROCESSING_EVENT_BEFORE]))
        {
            $this->aProcessingEvents[self::PROCESSING_EVENT_BEFORE]->call(array(&$bEndRequest));
        }

        if(!$bEndRequest)
        {
            try
            {
                $mResult = $this->getPluginManager()->processRequest();
            }
            catch(Exception $e)
            {
                // An exception was thrown while processing the request.
                // The request missed the corresponding handler function,
                // or an error occurred while attempting to execute the handler.
                // Replace the response, if one has been started and send a debug message.

                $xResponseManager->clear();
                $xResponseManager->append(new Response\Response());
                $xResponseManager->debug($e->getMessage());
                $mResult = false;

                if($e instanceof \Jaxon\Exception\Error)
                {
                    $sEvent = self::PROCESSING_EVENT_INVALID;
                    $aParams = array($e->getMessage());
                }
                else
                {
                    $sEvent = self::PROCESSING_EVENT_ERROR;
                    $aParams = array($e);
                }

                if(isset($this->aProcessingEvents[$sEvent]))
                {
                    // Call the processing event
                    $this->aProcessingEvents[$sEvent]->call($aParams);
                }
                else
                {
                    // The exception is not to be processed here.
                    throw $e;
                }
            }
        }
        // Clean the processing buffer
        if(($this->getOption('core.process.clean')))
        {
            $er = error_reporting(0);
            while (ob_get_level() > 0)
            {
                ob_end_clean();
            }
            error_reporting($er);
        }

        if($mResult === true)
        {
            // Handle after processing event
            if(isset($this->aProcessingEvents[self::PROCESSING_EVENT_AFTER]))
            {
                $bEndRequest = false;
                $this->aProcessingEvents[self::PROCESSING_EVENT_AFTER]->call(array($bEndRequest));
            }
            // If the called function returned no response, give the the global response instead
            if($xResponseManager->hasNoResponse())
            {
                $xResponseManager->append($this->getResponse());
            }
        }

        $xResponseManager->printDebug();

        if(($this->getOption('core.process.exit')))
        {
            $xResponseManager->sendOutput();
            exit();
        }
    }

    /**
     * Send the response output back to the browser
     *
     * @return void
     */
    public function sendResponse()
    {
        $this->getResponseManager()->sendOutput();
    }

    /**
     * Send the HTTP headers back to the browser
     *
     * @return void
     */
    public function sendHeaders()
    {
        $this->getResponseManager()->sendHeaders();
    }

    /**
     * Get the response output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->getResponseManager()->getOutput();
    }

    /**
     * Get the DI container
     *
     * @return Jaxon\Utils\Container
     */
    public function di()
    {
        return Container::getInstance();
    }
}
