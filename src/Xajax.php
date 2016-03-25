<?php

namespace Xajax;

use Xajax\Plugin\Manager as PluginManager;
use Xajax\Request\Manager as RequestManager;
use Xajax\Response\Manager as ResponseManager;

/*
	File: Xajax.php

	Main Xajax class and setup file.

	Title: Xajax class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: Xajax.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Section: Standard Definitions
*/

/*
	String: XAJAX_DEFAULT_CHAR_ENCODING

	Default character encoding used by both the <Xajax\Xajax> and
	<Xajax\Response\Response> classes.
*/
if(!defined ('XAJAX_DEFAULT_CHAR_ENCODING')) define ('XAJAX_DEFAULT_CHAR_ENCODING', 'utf-8');

/*
	String: XAJAX_PROCESSING_EVENT
	String: XAJAX_PROCESSING_EVENT_BEFORE
	String: XAJAX_PROCESSING_EVENT_AFTER
	String: XAJAX_PROCESSING_EVENT_INVALID

	Identifiers used to register processing events.  Processing events are essentially
	hooks into the xajax core that can be used to add functionality into the request
	processing sequence.
*/
if(!defined ('XAJAX_PROCESSING_EVENT')) define ('XAJAX_PROCESSING_EVENT', 'xajax processing event');
if(!defined ('XAJAX_PROCESSING_EVENT_BEFORE')) define ('XAJAX_PROCESSING_EVENT_BEFORE', 'beforeProcessing');
if(!defined ('XAJAX_PROCESSING_EVENT_AFTER')) define ('XAJAX_PROCESSING_EVENT_AFTER', 'afterProcessing');
if(!defined ('XAJAX_PROCESSING_EVENT_INVALID')) define ('XAJAX_PROCESSING_EVENT_INVALID', 'invalidRequest');

/*
	Class: xajax

	The xajax class uses a modular plug-in system to facilitate the processing
	of special Ajax requests made by a PHP page.  It generates Javascript that
	the page must include in order to make requests.  It handles the output
	of response commands (see <Xajax\Response\Response>).  Many flags and settings can be
	adjusted to effect the behavior of the xajax class as well as the client-side
	javascript.
*/
class Xajax
{
    use Translation\TranslatorTrait, Template\EngineTrait;

	/*
		Array: aSettings
		
		This array is used to store all the configuration settings that are set during
		the run of the script.  This provides a single data store for the settings
		in case we need to return the value of a configuration option for some reason.
		
		It is advised that individual plugins store a local copy of the settings they
		wish to track, however, settings are available via a reference to the <Xajax\Xajax> 
		object using <Xajax\Xajax->getConfiguration>.
	*/
	private $aSettings = array();

	/*
		Boolean: bErrorHandler
		
		This is a configuration setting that the main xajax object tracks.  It is used
		to enable an error handler function which will trap php errors and return them
		to the client as part of the response.  The client can then display the errors
		to the user if so desired.
	*/
	private $bErrorHandler;

	/*
		Array: aProcessingEvents
		
		Stores the processing event handlers that have been assigned during this run
		of the script.
	*/
	private $aProcessingEvents;

	/*
		Boolean: bExitAllowed
		
		A configuration option that is tracked by the main <Xajax\Xajax>object.  Setting this
		to true allows <Xajax\Xajax> to exit immediatly after processing a xajax request.  If
		this is set to false, xajax will allow the remaining code and HTML to be sent
		as part of the response.  Typically this would result in an error, however, 
		a response processor on the client side could be designed to handle this condition.
	*/
	private $bExitAllowed;
	
	/*
		Boolean: bCleanBuffer
		
		A configuration option that is tracked by the main <Xajax\Xajax> object.  Setting this
		to true allows <Xajax\Xajax> to clear out any pending output buffers so that the 
		<Xajax\Response\Response> is (virtually) the only output when handling a request.
	*/
	private $bCleanBuffer;
	
	/*
		String: sLogFile
	
		A configuration setting tracked by the main <Xajax\Xajax> object.  Set the name of the
		file on the server that you wish to have php error messages written to during
		the processing of <Xajax\Xajax> requests.	
	*/
	private $sLogFile;

	/*
		String: sCoreIncludeOutput
		
		This is populated with any errors or warnings produced while including the xajax
		core components.  This is useful for debugging core updates.
	*/
	private $sCoreIncludeOutput;
	
	/*
		Object: xPluginManager
		
		This stores a reference to the global <Xajax\\Plugin\Manager>
	*/
	private $xPluginManager;
	
	/*
		Object: xRequestManager
		
		Stores a reference to the global <Xajax\Request\Manager>
	*/
	private $xRequestManager;
	
	/*
		Object: xResponseManager
		
		Stores a reference to the global <Xajax\Response\Manager>
	*/
	private $xResponseManager;

	private $challengeResponse;
	
	/*
		Object: gxResponse
		
		Stores a reference to the global <Xajax\Response\Response>
	*/
	protected static $gxResponse = null;
	
	/*
		Object: gxTranslator
		
		Stores a reference to the global <Xajax\Translation\Translator>
	*/
	protected static $gxTranslator = null;
	
	/*
		Object: gxTemplate
		
		Stores a reference to the global <Xajax\Template\Engine>
	*/
	protected static $gxTemplate = null;

	/*
		Constructor: xajax

		Constructs a xajax instance and initializes the plugin system.
		
		Parameters:

		sRequestURI - (optional):  The <Xajax\Xajax->sRequestURI> to be used
			for calls back to the server.  If empty, xajax fills in the current
			URI that initiated this request.
	*/
	public function __construct($sRequestURI = null, $sLanguage = null)
	{
		$this->bErrorHandler = false;
		$this->aProcessingEvents = array();
		$this->bExitAllowed = true;
		$this->bCleanBuffer = true;
		$this->sLogFile = '';

		$this->xRequestManager = RequestManager::getInstance();
		$this->xResponseManager = ResponseManager::getInstance();
		$this->xPluginManager = PluginManager::getInstance();
		// Set the template engine in the plugin manager
		$this->xPluginManager->setTemplate(self::getTemplate());

        // Setup the translation manager
        $this->setTranslator(self::getTranslator());

		// The default configuration settings.
		$this->configureMany(
			array(
				'characterEncoding' => XAJAX_DEFAULT_CHAR_ENCODING,
				'decodeUTF8Input' => false,
				'outputEntities' => false,
				'responseType' => 'JSON',
				'defaultMode' => 'asynchronous',
				'defaultMethod' => 'POST',	// W3C: Method is case sensitive
				'wrapperPrefix' => 'xajax_',
				'debug' => false,
				'verbose' => false,
				'useUncompressedScripts' => false,
				'statusMessages' => false,
				'waitCursor' => true,
				'deferScriptGeneration' => false,
				'exitAllowed' => true,
				'errorHandler' => false,
				'cleanBuffer' => false,
				'allowBlankResponse' => false,
				'allowAllResponseTypes' => false,
				'generateStubs' => true,
				'logFile' => '',
				'timeout' => 6000,
				'version' => $this->getVersion()
				)
			);

		if(($sRequestURI))
			$this->configure('requestURI', $sRequestURI);
		else
			$this->configure('requestURI', $this->_detectURI());
		
		if(($sLanguage))
			$this->configure('language', $sLanguage);

		if(XAJAX_DEFAULT_CHAR_ENCODING != 'utf-8')
            $this->configure("decodeUTF8Input", true);
	}

	/**
	 * Set the PHP class autoloader
	 * 
	 * @param object		$xAutoLoader		The PHP class autoloader
	 *
	 * @return void
	 */
	public function setAutoLoader($xAutoLoader)
	{
		$this->xPluginManager->setAutoLoader($xAutoLoader);
	}

	/**
	 * Disable the PHP class autoloader
	 *
	 * @return void
	 */
	public function disableAutoLoad()
	{
		$this->xPluginManager->disableAutoLoad();
	}

	/*
		Function: getGlobalResponse

		Returns the <Xajax\Response\Response> object preconfigured with the encoding
		and entity settings from this instance of <Xajax\Xajax>.  This is used
		for singleton-pattern response development.

		Returns:

		<Xajax\Response\Response> : A <Xajax\Response\Response> object which can be used
			to return response commands.  See also the <Xajax\Response\Manager> class.
	*/
	public static function getGlobalResponse()
	{
		if(!self::$gxResponse)
        {
			self::$gxResponse = new Response\Response();
		}
		return self::$gxResponse;
	}

	/*
		Function: getTranslator

		Returns the global <Xajax\Translation\Translator> object.

		Returns:

		<Xajax\Translation\Translator> : A <Xajax\Translation\Translator>
			object which can be used to translate strings.
	*/
	public static function getTranslator()
	{
		if(!self::$gxTranslator)
        {
            $sTranslationsDir = __DIR__ . '/../translations';
            self::$gxTranslator = new Translation\Translator($sTranslationsDir);
		}
		return self::$gxTranslator;
	}

	/*
		Function: getTemplate

		Returns the global <Xajax\Template\Engine> object.

		Returns:

		<Xajax\Template\Engine> : A <Xajax\Template\Engine>
			object which can be used to render templates.
	*/
	public static function getTemplate()
	{
		if(!self::$gxTemplate)
        {
            $sTemplatesDir = __DIR__ . '/../templates';
            self::$gxTemplate = new Template\Engine($sTemplatesDir);
		}
		return self::$gxTemplate;
	}

	/*
		Function: getVersion

		Returns:

		string : The current xajax version.
	*/
	public static function getVersion()
	{
		return 'Xajax 0.7 alpha 1';
	}

	/*
		Function: register
		
		Call this function to register request handlers, including functions, 
		callable objects and events.  New plugins can be added that support
		additional registration methods and request processors.


		Parameters:
		
		$sType - (string): Type of request handler being registered; standard 
			options include:
				XAJAX_FUNCTION: a function declared at global scope.
				XAJAX_CALLABLE_OBJECT: an object who's methods are to be registered.
				XAJAX_EVENT: an event which will cause zero or more event handlers
					to be called.
				XAJAX_EVENT_HANDLER: register an event handler function.
				
		$sFunction || $objObject || $sEvent - (mixed):
			when registering a function, this is the name of the function
			when registering a callable object, this is the object being registered
			when registering an event or event handler, this is the name of the event
			
		$sIncludeFile || $aCallOptions || $sEventHandler
			when registering a function, this is the (optional) include file.
			when registering a callable object, this is an (optional) array
				of call options for the functions being registered.
			when registering an event handler, this is the name of the function.
	*/
	public function register($sType, $mArg)
	{
		$aArgs = func_get_args();
		$nArgs = func_num_args();

		if(XAJAX_PROCESSING_EVENT == $aArgs[0])
		{
			if($nArgs > 2)
			{
				$sEvent = $aArgs[1];
				$xUserFunction = $aArgs[2];
				if(!is_a($xUserFunction, 'Request\\Support\\UserFunction'))
					$xUserFunction = new Request\Support\UserFunction($xUserFunction);
				$this->aProcessingEvents[$sEvent] = $xUserFunction;
				return true;
			}
            else
            {
                // Todo: return error
            }
		}
		
		return $this->xPluginManager->register($aArgs);
	}

	/**
	 * Add a path to the class directories
	 *
	 * @param string		$sPath			The path to the directory
	 * @param string|null	$sNamespace		The associated namespace
	 *
	 * @return boolean
	 */
	public function addClassDir($sPath, $sNamespace = null)
	{
		return $this->xPluginManager->addClassDir($sPath, $sNamespace);
	}

	/**
	 * Register callable objects from all class directories
	 *
	 * @return void
	 */
	public function registerClasses()
	{
		return $this->xPluginManager->registerClasses();
	}

	/**
	 * Set the URI of the Xajax javascript library files
	 * 
	 * @param string		$sJsLibURI		The URI of the Xajax javascript library files
	 *
	 * @return void
	 */
	public function setJavascriptURI($sJsLibURI)
	{
		$this->xPluginManager->setJavascriptURI($sJsLibURI);
	}
	
	/*
		Function: mergeJavascript
		
		Merge and minify the javascript code generated by Xajax.
		
		Parameters:
		
		sJsAppURI - (string):  The URI where the generated file will be located.
		sJsAppDir - (string):  The dir where the generated file will be located.
		bMinifyJs - (boolean):  Shall the generated file also be minified.
	*/
	public function mergeJavascript($sJsAppURI, $sJsAppDir, $bMinifyJs = false)
	{
		$this->xPluginManager->mergeJavascript($sJsAppURI, $sJsAppDir, $bMinifyJs);
	}
	
	/*
		Function: configure
		
		Call this function to set options that will effect the processing of 
		xajax requests.  Configuration settings can be specific to the xajax
		core, request processor plugins and response plugins.


		Parameters:
		
		Options include:
			javascript URI - (string): The path to the folder that contains the 
				xajax javascript files.
			errorHandler - (boolean): true to enable the xajax error handler, see
				<Xajax\Xajax->bErrorHandler>
			exitAllowed - (boolean): true to allow xajax to exit after processing
				a request.  See <Xajax\Xajax->bExitAllowed> for more information.
	*/
	public function configure($sName, $mValue)
	{
        switch($sName)
        {
        case 'errorHandler':
			if($mValue === true || $mValue === false)
				$this->bErrorHandler = $mValue;
            break;
		case 'exitAllowed':
			if($mValue === true || $mValue === false)
				$this->bExitAllowed = $mValue;
            break;
		case 'cleanBuffer':
			if($mValue === true || $mValue === false)
				$this->bCleanBuffer = $mValue;
            break;
		case 'logFile':
			$this->sLogFile = $mValue;
            break;
        default: break;
        }

		self::getTranslator()->configure($sName, $mValue);
		$this->xRequestManager->configure($sName, $mValue);
		$this->xPluginManager->configure($sName, $mValue);
		$this->xResponseManager->configure($sName, $mValue);

		$this->aSettings[$sName] = $mValue;
	}

	/*
		Function: configureMany
		
		Set an array of configuration options.

		Parameters:
		
		$aOptions - (array): Associative array of configuration settings
	*/
	public function configureMany($aOptions)
	{
		foreach($aOptions as $sName => $mValue)
			$this->configure($sName, $mValue);
	}

	/*
		Function: getConfiguration
		
		Get the current value of a configuration setting that was previously set
		via <Xajax\Xajax->configure> or <Xajax\Xajax->configureMany>

		Parameters:
		
		$sName - (string): The name of the configuration setting
				
		Returns:
		
		$mValue : (mixed):  The value of the setting if set, null otherwise.
	*/
	public function getConfiguration($sName)
	{
		if(isset($this->aSettings[$sName]))
			return $this->aSettings[$sName];
		return NULL;
	}

	/*
		Function: canProcessRequest
		
		Determines if a call is a xajax request or a page load request.
		
		Return:
		
		boolean - True if this is a xajax request, false otherwise.
	*/
	public function canProcessRequest()
	{
		return $this->xPluginManager->canProcessRequest();
	}

	/*
		Function: VerifySession

		Ensure that an active session is available (primarily used
		for storing challenge / response codes).
	*/
	private function verifySession()
	{
		$sessionID = session_id();
		if($sessionID === '')
		{
			$this->xResponseManager->debug('Must enable sessions to use challenge/response.');
			return false;
		}
		return true;
	}

	private function loadChallenges($sessionKey)
	{
		$challenges = array();

		if(isset($_SESSION[$sessionKey]))
			$challenges = $_SESSION[$sessionKey];

		return $challenges;
	}

	private function saveChallenges($sessionKey, $challenges)
	{
		if(count($challenges) > 10)
			array_shift($challenges);

		$_SESSION[$sessionKey] = $challenges;
	}

	private function makeChallenge($algo, $value)
	{
		// TODO: Move to configuration option
		if(!$algo)
			$algo = 'md5';
		// TODO: Move to configuration option
		if(!$value)
			$value = rand(100000, 999999);

		return hash($algo, $value);
	}

	/*
		Function: challenge

		Call this from the top of a xajax enabled request handler
		to introduce a challenge and response cycle into the request
		response process.

		NOTE:  Sessions must be enabled to use this feature.
	*/
	public function challenge($algo=null, $value=null)
	{
		if(!$this->verifySession())
			return false;

		// TODO: Move to configuration option
		$sessionKey = 'xajax_challenges';

		$challenges = $this->loadChallenges($sessionKey);

		if(isset($this->challengeResponse))
		{
			$key = array_search($this->challengeResponse, $challenges);

			if($key !== false)
			{
				unset($challenges[$key]);
				$this->saveChallenges($sessionKey, $challenges);
				return true;
			}
		}

		$challenge = $this->makeChallenge($algo, $value);

		$challenges[] = $challenge;

		$this->saveChallenges($sessionKey, $challenges);

		header("challenge: {$challenge}");

		return false;
	}

	/*
		Function: processRequest

		If this is a xajax request (see <Xajax\Xajax->canProcessRequest>), call the
		requested PHP function, build the response and send it back to the
		browser.

		This is the main server side engine for xajax.  It handles all the
		incoming requests, including the firing of events and handling of the
		response.  If your RequestURI is the same as your web page, then this
		function should be called before ANY headers or HTML is output from
		your script.

		This function may exit, if a request is processed.  See <Xajax\Xajax->bAllowExit>
	*/
	public function processRequest()
	{
		if(isset($_SERVER['HTTP_CHALLENGE_RESPONSE']))
			$this->challengeResponse = $_SERVER['HTTP_CHALLENGE_RESPONSE'];

//SkipDebug
		// Check to see if headers have already been sent out, in which case we can't do our job
		if(headers_sent($filename, $linenumber))
        {
			echo "Output has already been sent to the browser at {$filename}:{$linenumber}.\n";
			echo 'Please make sure the command $xajax->processRequest() is placed before this.';
			exit();
		}
//EndSkipDebug

		if($this->canProcessRequest())
		{
			// Use xajax error handler if necessary
			if($this->bErrorHandler) {
				$GLOBALS['xajaxErrorHandlerText'] = "";
				set_error_handler("xajaxErrorHandler");
			}
			
			$mResult = true;

			// handle beforeProcessing event
			if(isset($this->aProcessingEvents[XAJAX_PROCESSING_EVENT_BEFORE]))
			{
				$bEndRequest = false;
				$this->aProcessingEvents[XAJAX_PROCESSING_EVENT_BEFORE]->call(array(&$bEndRequest));
				$mResult = (false === $bEndRequest);
			}

			if(true === $mResult)
				$mResult = $this->xPluginManager->processRequest();

			if(true === $mResult)
			{
				if($this->bCleanBuffer) {
					$er = error_reporting(0);
					while (ob_get_level() > 0) ob_end_clean();
					error_reporting($er);
				}

				// handle afterProcessing event
				if(isset($this->aProcessingEvents[XAJAX_PROCESSING_EVENT_AFTER]))
				{
					$bEndRequest = false;

					$this->aProcessingEvents[XAJAX_PROCESSING_EVENT_AFTER]->call(
						array($bEndRequest)
						);

					if(true === $bEndRequest)
					{
						$this->xResponseManager->clear();
						$this->xResponseManager->append($aResult[1]);
					}
				}
			}
			else if(is_string($mResult))
			{
				if($this->bCleanBuffer) {
					$er = error_reporting(0);
					while (ob_get_level() > 0) ob_end_clean();
					error_reporting($er);
				}

				// $mResult contains an error message
				// the request was missing the cooresponding handler function
				// or an error occurred while attempting to execute the
				// handler.  replace the response, if one has been started
				// and send a debug message.

				$this->xResponseManager->clear();
				$this->xResponseManager->append(new Response\Response());

				// handle invalidRequest event
				if(isset($this->aProcessingEvents[XAJAX_PROCESSING_EVENT_INVALID]))
					$this->aProcessingEvents[XAJAX_PROCESSING_EVENT_INVALID]->call();
				else
					$this->xResponseManager->debug($mResult);
			}

			if($this->bErrorHandler)
			{
				$sErrorMessage = $GLOBALS['xajaxErrorHandlerText'];
				if(!empty($sErrorMessage))
				{
					if(0 < strlen($this->sLogFile))
					{
						$fH = @fopen($this->sLogFile, "a");
						if(NULL != $fH)
						{
							fwrite($fH, $this->trans('errors.debug.ts-message', array(
									'timestamp' => strftime("%b %e %Y %I:%M:%S %p"),
									'message' => $sErrorMessage)));
							fclose($fH);
						}
						else
						{
							$this->xResponseManager->debug($this->trans('errors.debug.write-log', array('file' => $this->sLogFile)));
						}
					}
					$this->xResponseManager->debug($this->trans('errors.debug.message', array('message' => $sErrorMessage)));
				}
			}

			$this->xResponseManager->send();

			if($this->bErrorHandler)
				restore_error_handler();
			if($this->bExitAllowed)
				exit();
		}
	}

	/*
		Function: getJavascript
		
		Returns the Xajax Javascript header and wrapper code to be printed into the page.
		The returned code should be printed between the HEAD and /HEAD tags at the top of the page.
		
		The javascript code returned by this function is dependent on the plugins
		that are included and the functions and classes that are registered.
	*/
	public function getJavascript()
	{
		return $this->xPluginManager->getClientScript();
	}

	/*
		Function: printJavascript
		
		Prints the xajax Javascript header and wrapper code into your page.
		This should be used to print the javascript code between the HEAD
		and /HEAD tags at the top of the page.
		
		The javascript code output by this function is dependent on the plugins
		that are included and the functions that are registered.
		
	*/
	public function printJavascript()
	{
		print $this->getJavascript();
	}

	/*
		Function: getJsInclude
	
		Returns the javascript header includes for response plugins.
	
		Parameters:
	 */
	public function getJsInclude()
	{
		return $this->xPluginManager->getJsInclude();
	}
	
	/*
		Function: getCssInclude
	
		Returns the CSS header includes for response plugins.
	
		Parameters:
	 */
	public function getCssInclude()
	{
		return $this->xPluginManager->getCssInclude();
	}

	/*
		Function: plugin
		
		Provides access to registered response plugins. Pass the plugin name as the
		first argument and the plugin object will be returned.  You can then
		access the methods of the plugin directly.
		
		Parameters:
		
		sName - (string):  Name of the plugin.
			
		Returns:
		
		object - The plugin specified by sName.
	*/
	public function plugin($sName)
	{
		$xPlugin = $this->xPluginManager->getResponsePlugin($sName);
		if(!$xPlugin)
		{
			return false;
		}
		$xPlugin->setResponse($this);
		return $xPlugin;
	}
	
	/*
		Function: _detectURI

		Returns the current requests URL based upon the SERVER vars.

		Returns:

		string : The URL of the current request.
	*/
	private function _detectURI()
	{
		$aURL = array();
		// Try to get the request URL
		if(!empty($_SERVER['REQUEST_URI']))
		{
			$_SERVER['REQUEST_URI'] = str_replace(array('"',"'",'<','>'), array('%22','%27','%3C','%3E'), $_SERVER['REQUEST_URI']);
			$aURL = parse_url($_SERVER['REQUEST_URI']);
		}

		// Fill in the empty values
		if(empty($aURL['scheme']))
		{
			if(!empty($_SERVER['HTTP_SCHEME']))
			{
				$aURL['scheme'] = $_SERVER['HTTP_SCHEME'];
			}
			else
			{
				$aURL['scheme'] = ((!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? 'https' : 'http');
			}
		}

		if(empty($aURL['host']))
		{
			if(!empty($_SERVER['HTTP_X_FORWARDED_HOST']))
			{
				if(strpos($_SERVER['HTTP_X_FORWARDED_HOST'], ':') > 0)
				{
					list($aURL['host'], $aURL['port']) = explode(':', $_SERVER['HTTP_X_FORWARDED_HOST']);
				}
				else
				{
					$aURL['host'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
				}
			}
			else if(!empty($_SERVER['HTTP_HOST']))
			{
				if(strpos($_SERVER['HTTP_HOST'], ':') > 0)
				{
					list($aURL['host'], $aURL['port']) = explode(':', $_SERVER['HTTP_HOST']);
				}
				else
				{
					$aURL['host'] = $_SERVER['HTTP_HOST'];
				}
			}
			else if(!empty($_SERVER['SERVER_NAME']))
			{
				$aURL['host'] = $_SERVER['SERVER_NAME'];
			}
			else
			{
				throw new Exception\DetectUri();
			}
		}

		if(empty($aURL['port']) && !empty($_SERVER['SERVER_PORT']))
		{
			$aURL['port'] = $_SERVER['SERVER_PORT'];
		}

		if(!empty($aURL['path']) && strlen(basename($aURL['path'])) == 0)
		{
			unset($aURL['path']);
		}
		
		if(empty($aURL['path']))
		{
			$sPath = array();
			if(!empty($_SERVER['PATH_INFO']))
			{
				$sPath = parse_url($_SERVER['PATH_INFO']);
			}
			else
			{
				$sPath = parse_url($_SERVER['PHP_SELF']);
			}
			if(isset($sPath['path']))
			{
				$aURL['path'] = str_replace(array('"',"'",'<','>'), array('%22','%27','%3C','%3E'), $sPath['path']);
			}
			unset($sPath);
		}

		if(empty($aURL['query']) && !empty($_SERVER['QUERY_STRING']))
		{
			$aURL['query'] = $_SERVER['QUERY_STRING'];
		}

		if(!empty($aURL['query']))
		{
			$aURL['query'] = '?'.$aURL['query'];
		}

		// Build the URL: Start with scheme, user and pass
		$sURL = $aURL['scheme'].'://';
		if(!empty($aURL['user']))
		{
			$sURL.= $aURL['user'];
			if(!empty($aURL['pass']))
			{
				$sURL.= ':'.$aURL['pass'];
			}
			$sURL.= '@';
		}

		// Add the host
		$sURL.= $aURL['host'];

		// Add the port if needed
		if(!empty($aURL['port']) 
			&& (($aURL['scheme'] == 'http' && $aURL['port'] != 80) 
			|| ($aURL['scheme'] == 'https' && $aURL['port'] != 443)))
		{
			$sURL.= ':'.$aURL['port'];
		}

		// Add the path and the query string
		$sURL.= $aURL['path'].@$aURL['query'];

		// Clean up
		unset($aURL);
		
		$aURL = explode("?", $sURL);
		
		if(1 < count($aURL))
		{
			$aQueries = explode("&", $aURL[1]);

			foreach($aQueries as $sKey => $sQuery)
			{
				if("xjxGenerate" == substr($sQuery, 0, 11))
					unset($aQueries[$sKey]);
			}
			
			$sQueries = implode("&", $aQueries);
			
			$aURL[1] = $sQueries;
			
			$sURL = implode("?", $aURL);
		}

		return $sURL;
	}

}
