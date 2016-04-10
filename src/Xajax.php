<?php

namespace Xajax;

use Xajax\Plugin\Manager as PluginManager;
use Xajax\Request\Manager as RequestManager;
use Xajax\Response\Manager as ResponseManager;

use Xajax\Utils\URI;

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
	use \Xajax\Utils\ContainerTrait;

	/*
		Array: aProcessingEvents
		
		Stores the processing event handlers that have been assigned during this run
		of the script.
	*/
	private $aProcessingEvents;

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
		Constructor: xajax

		Constructs a xajax instance and initializes the plugin system.
		
		Parameters:

		sRequestURI - (optional):  The <Xajax\Xajax->sRequestURI> to be used
			for calls back to the server.  If empty, xajax fills in the current
			URI that initiated this request.
	*/
	public function __construct($sRequestURI = null, $sLanguage = null)
	{
		$this->aProcessingEvents = array();

		$sTranslationDir = __DIR__ . '/../translations';
		$sTemplateDir = __DIR__ . '/../templates';
		Utils\Container::getInstance()->init(realpath($sTranslationDir), realpath($sTemplateDir));

		$this->xRequestManager = RequestManager::getInstance();
		$this->xResponseManager = ResponseManager::getInstance();
		$this->xPluginManager = PluginManager::getInstance();

		$this->setDefaultOptions();
		if(($sRequestURI))
			$this->setOption('requestURI', $sRequestURI);
		else
			$this->setOption('requestURI', URI::detect());
		if(($sLanguage))
			$this->setOption('language', $sLanguage);
		if(XAJAX_DEFAULT_CHAR_ENCODING != 'utf-8')
			$this->setOption("decodeUTF8Input", true);
	}

	/**
	 * Set the default options of all components of the library
	 *
	 * @return void
	 */
	private function setDefaultOptions()
	{
		// The default configuration settings.
		$this->setOptions(array(
			'characterEncoding' => XAJAX_DEFAULT_CHAR_ENCODING,
			'decodeUTF8Input' => false,
			'outputEntities' => false,
			'responseType' => 'JSON',
			'defaultMode' => 'asynchronous',
			'defaultMethod' => 'POST',	// W3C: Method is case sensitive
			'wrapperPrefix' => 'xajax_',
			'debug' => false,
			'verbose' => false,
			'statusMessages' => false,
			'waitCursor' => true,
			'exitAllowed' => true,
			'errorHandler' => false,
			'cleanBuffer' => false,
			'allowBlankResponse' => false,
			'allowAllResponseTypes' => false,
			'generateStubs' => true,
			'logFile' => '',
			'timeout' => 6000,
			'version' => $this->getVersion()
		));

		// Main Xajax object options
		$this->setOptions(array(
			'requestURI' => '',
			'errorHandler' => false,
			'exitAllowed' => true,
			'cleanBuffer' => true,
			'logFile' => '',
		));

		// Plugins options
		$this->setOptions(array(
			'wrapperPrefix' => 'xajax_',
			'eventPrefix' => 'event_',
			'scriptDefferal' => '',
			'outputEntities' => false,
			'decodeUTF8Input' => false,
			'characterEncoding' => 'UTF-8',
			'statusMessages' => 'false',
			'waitCursor' => 'true',
			'version' => 'unknown',
			'defaultMode' => 'asynchronous',
			'defaultMethod' => 'POST',	// W3C: Method is case sensitive
			'debug' => false,
			'verboseDebug' => false,
			'scriptLoadTimeout' => 2000,
			'language' => 'en',
			'responseQueueSize' => null,
			'debugOutputID' => null,
		));
	}

	/**
	 * Set Xajax to use the Composer autoloader
	 *
	 * @return void
	 */
	public function useComposerAutoLoader()
	{
		$xAutoLoader = require (__DIR__ . '/../../../autoload.php');
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
		
		sJsAppDir - (string):  The dir where the generated file will be located.
		sJsAppURI - (string):  The URI where the generated file will be located.
		bMinifyJs - (boolean):  Shall the generated file also be minified.
	*/
	public function mergeJavascript($sJsAppDir, $sJsAppURI, $bMinifyJs = true)
	{
		$this->xPluginManager->mergeJavascript($sJsAppDir, $sJsAppURI, $bMinifyJs);
	}

	/*
		Function: configureMany
		
		Set an array of configuration options.

		Parameters:
		
		$aOptions - (array): Associative array of configuration settings
	*/
	public function configureMany(array $aOptions)
	{
		$this->setOptions($aOptions);
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
	public function configure($sName, $xValue)
	{
		$this->setOption($sName, $xValue);
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
		return $this->getOption($sName);
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
			echo $this->trans('errors.output.already-sent', array(
				'location' => $filename . ':' . $linenumber
			)), "\n", $this->trans('errors.output.advice');
			exit();
		}
//EndSkipDebug

		if($this->canProcessRequest())
		{
			// Use xajax error handler if necessary
			if(($this->getOption('errorHandler')))
			{
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
				if(($this->getOption('cleanBuffer')))
				{
					$er = error_reporting(0);
					while (ob_get_level() > 0) ob_end_clean();
					error_reporting($er);
				}

				// handle afterProcessing event
				if(isset($this->aProcessingEvents[XAJAX_PROCESSING_EVENT_AFTER]))
				{
					$bEndRequest = false;

					$this->aProcessingEvents[XAJAX_PROCESSING_EVENT_AFTER]->call(array($bEndRequest));

					if($bEndRequest === true)
					{
						$this->xResponseManager->clear();
						$this->xResponseManager->append($aResult[1]);
					}
				}
			}
			else if(is_string($mResult))
			{
				if(($this->getOption('cleanBuffer')))
				{
					$er = error_reporting(0);
					while (ob_get_level() > 0)
					{
						ob_end_clean();
					}
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

			if(($this->getOption('errorHandler')))
			{
				$sErrorMessage = $GLOBALS['xajaxErrorHandlerText'];
				if(!empty($sErrorMessage))
				{
					if(strlen($this->getOption('logFile')) > 0)
					{
						$fH = @fopen($this->getOption('logFile'), "a");
						if(null != $fH)
						{
							fwrite($fH, $this->trans('errors.debug.ts-message', array(
								'timestamp' => strftime("%b %e %Y %I:%M:%S %p"),
								'message' => $sErrorMessage
							)));
							fclose($fH);
						}
						else
						{
							$this->xResponseManager->debug($this->trans('errors.debug.write-log',
								array('file' => $this->getOption('logFile'),)));
						}
					}
					$this->xResponseManager->debug($this->trans('errors.debug.message', array('message' => $sErrorMessage)));
				}
			}

			$this->xResponseManager->send();

			if(($this->getOption('errorHandler')))
			{
				restore_error_handler();
			}
			if(($this->getOption('exitAllowed')))
			{
				exit();
			}
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
}
