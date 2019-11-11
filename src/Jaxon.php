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

use Jaxon\Plugin\Plugin;
use Jaxon\Utils\DI\Container;
use Jaxon\Utils\Config\Reader as ConfigReader;

class Jaxon
{
    use Features\Config;

    /**
     * Package version number
     *
     * @var string
     */
    private $sVersion = 'Jaxon 3.0.0';

    /*
     * Processing events
     */
    const PROCESSING_EVENT = 'ProcessingEvent';
    const PROCESSING_EVENT_BEFORE = 'BeforeProcessing';
    const PROCESSING_EVENT_AFTER = 'AfterProcessing';
    const PROCESSING_EVENT_INVALID = 'InvalidRequest';
    const PROCESSING_EVENT_ERROR = 'ProcessingError';

    /*
     * Request plugins
     */
    const CALLABLE_CLASS = 'CallableClass';
    const CALLABLE_DIR = 'CallableDir';
    const CALLABLE_FUNCTION = 'CallableFunction';
    // For uploaded files.
    const FILE_UPLOAD = 'FileUpload';
    // For compatibility with previous versions
    const CALLABLE_OBJECT = 'CallableClass'; // Same as CALLABLE_CLASS
    const USER_FUNCTION = 'CallableFunction'; // Same as CALLABLE_FUNCTION

    /**
     * A static instance on this class
     *
     * @var Jaxon
     */
    private static $xInstance = null;

    /**
     * The DI container
     *
     * @var Container
     */
    private static $xContainer = null;

    /**
     * Get the static instance
     *
     * @return Jaxon
     */
    public static function getInstance()
    {
        if(self::$xInstance == null)
        {
            self::$xInstance = new Jaxon();
        }
        return self::$xInstance;
    }

    /**
     * The constructor
     */
    public function __construct()
    {
        if(self::$xContainer == null)
        {
            self::$xContainer = new Container();
        }
        $this->setDefaultOptions();
    }

    /**
     * Get the DI container
     *
     * @return Container
     */
    public function di()
    {
        return self::$xContainer;
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
     * Get the config reader
     *
     * @return ConfigReader
     */
    public function config()
    {
        return $this->di()->get(ConfigReader::class);
    }

    /**
     * Set the default options of all components of the library
     *
     * @return void
     */
    private function setDefaultOptions()
    {
        // The default configuration settings.
        $this->di()->getConfig()->setOptions([
            'core.version'                      => $this->getVersion(),
            'core.language'                     => 'en',
            'core.encoding'                     => 'utf-8',
            'core.decode_utf8'                  => false,
            'core.prefix.function'              => 'jaxon_',
            'core.prefix.class'                 => 'Jaxon',
            // 'core.request.uri'               => '',
            'core.request.mode'                 => 'asynchronous',
            'core.request.method'               => 'POST',    // W3C: Method is case sensitive
            'core.response.send'                => true,
            'core.response.merge.ap'            => true,
            'core.response.merge.js'            => true,
            'core.debug.on'                     => false,
            'core.debug.verbose'                => false,
            'core.process.exit'                 => true,
            'core.process.clean'                => false,
            'core.process.timeout'              => 6000,
            'core.error.handle'                 => false,
            'core.error.log_file'               => '',
            'core.jquery.no_conflict'           => false,
            'js.lib.output_id'                  => 0,
            'js.lib.queue_size'                 => 0,
            'js.lib.load_timeout'               => 2000,
            'js.lib.show_status'                => false,
            'js.lib.show_cursor'                => true,
            'js.app.dir'                        => '',
            'js.app.minify'                     => true,
            'js.app.options'                    => '',
        ]);
    }

    /**
     * Get the Global Response object
     *
     * @return \Jaxon\Response\Response
     */
    public function getResponse()
    {
        return $this->di()->getResponse();
    }

    /**
     * Create a new Jaxon response object
     *
     * @return \Jaxon\Response\Response
     */
    public function newResponse()
    {
        return $this->di()->newResponse();
    }
    /**
     * Register a plugin
     *
     * Below is a table for priorities and their description:
     * - 0 thru 999: Plugins that are part of or extensions to the jaxon core
     * - 1000 thru 8999: User created plugins, typically, these plugins don't care about order
     * - 9000 thru 9999: Plugins that generally need to be last or near the end of the plugin list
     *
     * @param Plugin    $xPlugin        An instance of a plugin
     * @param integer   $nPriority      The plugin priority, used to order the plugins
     *
     * @return void
     */
    public function registerPlugin(Plugin $xPlugin, $nPriority = 1000)
    {
        $this->di()->getPluginManager()->registerPlugin($xPlugin, $nPriority);
    }

    /**
     * Register request handlers, including functions, callable classes and directories.
     *
     * @param string        $sType            The type of request handler being registered
     *        Options include:
     *        - Jaxon::USER_FUNCTION: a function declared at global scope
     *        - Jaxon::CALLABLE_CLASS: a class who's methods are to be registered
     *        - Jaxon::CALLABLE_DIR: a directory containing classes to be registered
     *        - Jaxon::PROCESSING_EVENT:
     * @param string        $sCallable
     *        When registering a function, this is the name of the function
     *        When registering a callable class, this is the class name
     *        When registering a callable directory, this is the full path to the directory
     *        When registering an event, this is the event name
     * @param array|string  $xOptions
     *        When registering a function, this is an (optional) array
     *             of call options, or the (optional) include file
     *        When registering a callable class, this is an (optional) array
     *             of call options for the class methods
     *        When registering a callable directory, this is an (optional) array
     *             of call options for the class methods, or the (optional) namespace
     *
     * @return mixed
     */
    public function register($sType, $sCallable, $xOptions = [])
    {
        if($sType == Jaxon::PROCESSING_EVENT)
        {
            $sEvent = $sCallable;
            $xCallback = $xOptions;
            switch($sEvent)
            {
            case Jaxon::PROCESSING_EVENT_BEFORE:
                $this->callback()->before($xCallback);
                break;
            case Jaxon::PROCESSING_EVENT_AFTER:
                $this->callback()->after($xCallback);
                break;
            case Jaxon::PROCESSING_EVENT_INVALID:
                $this->callback()->invalid($xCallback);
                break;
            case Jaxon::PROCESSING_EVENT_ERROR:
                $this->callback()->error($xCallback);
                break;
            default:
                break;
            }
            return;
        }
        return $this->di()->getPluginManager()->registerCallable($sType, $sCallable, $xOptions);
    }

    /**
     * Get an instance of a registered class
     *
     * @param string        $sClassName         The class name
     *
     * @return mixed
     */
    public function instance($sClassName)
    {
        $xCallable = $this->di()->getCallableRepository()->getCallableObject($sClassName);
        return ($xCallable) ? $xCallable->getRegisteredObject() : null;
    }

    /**
     * Get a request to a registered class
     *
     * @param string        $sClassName         The class name
     *
     * @return \Jaxon\Request\Factory\CallableClass\Request
     */
    public function request($sClassName)
    {
        $xInstance = $this->instance($sClassName);
        return ($xInstance) ? $xInstance->rq() : null;
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
        return $this->di()->getCodeGenerator()->getScript($bIncludeJs, $bIncludeCss);
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
        return $this->di()->getCodeGenerator()->getJs();
    }

    /**
     * Return the CSS header code and file includes
     *
     * @return string
     */
    public function getCss()
    {
        return $this->di()->getCodeGenerator()->getCss();
    }

    /**
     * Determine if a call is a jaxon request or a page load request
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        return $this->di()->getRequestHandler()->canProcessRequest();
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
        return $this->di()->getRequestHandler()->processRequest();
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
        return $this->di()->getPluginManager()->getResponsePlugin($sName);
    }

    /**
     * Get the upload plugin
     *
     * @return \Jaxon\Request\Plugin\FileUpload
     */
    public function upload()
    {
        return $this->di()->getPluginManager()->getRequestPlugin(self::FILE_UPLOAD);
    }

    /**
     * Get the request callback manager
     *
     * @return \Jaxon\Request\Handler\Callback
     */
    public function callback()
    {
        return $this->di()->getRequestHandler()->getCallbackManager();
    }

    /**
     * Get the dialog wrapper
     *
     * @return \Jaxon\Utils\Dialogs\Dialog
     */
    public function dialog()
    {
        return $this->di()->getDialog();
    }

    /**
     * Get the template engine
     *
     * @return \Jaxon\Utils\Template\Engine
     */
    public function template()
    {
        return $this->di()->getTemplateEngine();
    }

    /**
     * Get the App instance
     *
     * @return \Jaxon\App\App
     */
    public function app()
    {
        return $this->di()->getApp();
    }

    /**
     * Get the view renderer
     *
     * @return Jaxon\Utils\View\Renderer
     */
    public function view()
    {
        return $this->di()->getViewRenderer();
    }

    /**
     * Get the session manager
     *
     * @return Jaxon\Contracts\Session
     */
    public function session()
    {
        return $this->di()->getSessionManager();
    }
}
