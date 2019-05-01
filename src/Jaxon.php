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

use Jaxon\Config\Reader as ConfigReader;
use Jaxon\DI\Container;
use Jaxon\Utils\URI;

use Exception;
use Closure;

class Jaxon
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Translator;
    use \Jaxon\Utils\Traits\Paginator;

    use Traits\Autoload;
    use Traits\Plugin;
    use Traits\Upload;
    use Traits\Template;
    use Traits\App;

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
     * Request methods
     */
    const METHOD_UNKNOWN = 0;
    const METHOD_GET = 1;
    const METHOD_POST = 2;

    /*
     * Request plugins
     */
    // For objects who's methods will be callable from the browser.
    const CALLABLE_CLASS = 'CallableClass';
    const CALLABLE_DIR = 'CallableDir';
    const CALLABLE_OBJECT = 'CallableClass'; // Same as CALLABLE_CLASS
    // For functions available at global scope, or from an instance of an object.
    const USER_FUNCTION = 'UserFunction';
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
     * The constructor
     */
    public function __construct()
    {
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
     * Get the config reader
     *
     * @return Jaxon\Config\Reader
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
     * Register request handlers, including functions, callable classes and directories.
     *
     * @param string        $sType            The type of request handler being registered
     *        Options include:
     *        - Jaxon::USER_FUNCTION: a function declared at global scope
     *        - Jaxon::CALLABLE_CLASS: a class who's methods are to be registered
     *        - Jaxon::CALLABLE_DIR: a directory containing classes to be registered
     * @param string        $sCallable
     *        When registering a function, this is the name of the function
     *        When registering a callable class, this is the class name
     *        When registering a callable directory, this is the full path to the directory
     * @param array|string  $aOptions | $sIncludeFile | $sNamespace
     *        When registering a function, this is an (optional) array
     *             of call options, or the (optional) include file
     *        When registering a callable class, this is an (optional) array
     *             of call options for the class methods
     *        When registering a callable directory, this is an (optional) array
     *             of call options for the class methods, or the (optional) namespace
     *
     * @return mixed
     */
    public function register($sType, $sCallable, $aOptions = [])
    {
        return $this->getPluginManager()->register($sType, $sCallable, $aOptions);
    }

    /**
     * Read config options from a config file and setup the library
     *
     * @param string        $sConfigFile        The full path to the config file
     *
     * @return Jaxon
     */
    public function setup($sConfigFile)
    {
        $aConfigOptions = $this->config()->read($sConfigFile);

        // Setup the config options into the library.
        $sLibKey = 'lib';
        $xLibConfig = $this->di()->getConfig();
        $xLibConfig->setOptions($aConfigOptions, $sLibKey);

        $sAppKey = 'app';
        $xAppConfig = new \Jaxon\Config\Config();
        $xAppConfig->setOptions($aConfigOptions, $sAppKey);

        // Register user functions and classes
        $this->getPluginManager()->registerFromConfig($xAppConfig);

        return $this;
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
        $xCodeGenerator = $this->di()->getCodeGenerator();
        if(($bIncludeCss))
        {
            $sCode .= $xCodeGenerator->getCss() . "\n";
        }
        if(($bIncludeJs))
        {
            $sCode .= $xCodeGenerator->getJs() . "\n";
        }
        $sCode .= $xCodeGenerator->getScript();
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
        return $this->getRequestHandler()->canProcessRequest();
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
        return $this->getRequestHandler()->processRequest();
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
     * @return Jaxon\DI\Container
     */
    public function di()
    {
        return Container::getInstance();
    }
}
