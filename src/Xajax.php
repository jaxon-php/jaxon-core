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
	Class: xajax

	The xajax class uses a modular plug-in system to facilitate the processing
	of special Ajax requests made by a PHP page.  It generates Javascript that
	the page must include in order to make requests.  It handles the output
	of response commands (see <Xajax\Response\Response>).  Many flags and settings can be
	adjusted to effect the behavior of the xajax class as well as the client-side
	javascript.
*/
class Xajax extends Base
{
	use \Xajax\Utils\ContainerTrait;

	/*
		Array: aOptionMappings
		
		Maps the previous config options to the current ones, so the library can still accept them.
	*/
	private $aOptionMappings;

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
		String: sErrorMessage
		
		Stores the error message when the Xajax error handling system is enabled
	*/
	private $sErrorMessage = '';

	/*
		Constructor: xajax

		Constructs a xajax instance and initializes the plugin system.
	*/
	private function __construct()
	{
		$this->aProcessingEvents = array();

		$sTranslationDir = realpath(__DIR__ . '/../translations');
		$sTemplateDir = realpath(__DIR__ . '/../templates');
		Utils\Container::getInstance()->init($sTranslationDir, $sTemplateDir);

		$this->xRequestManager = RequestManager::getInstance();
		$this->xResponseManager = ResponseManager::getInstance();
		$this->xPluginManager = PluginManager::getInstance();

		$this->setDefaultOptions();
		$this->aOptionMappings = array(
			'language'					=> 'core.language',
			'version'					=> 'core.version',
			'wrapperPrefix'				=> array('core.prefix.function', 'core.prefix.class'),
			'eventPrefix'				=> 'core.prefix.event',
			'responseQueueSize'			=> 'core.response.queue_size',
			'timeout'					=> 'core.response.timeout',
			'requestURI'				=> 'core.request.uri',
			'defaultMode'				=> 'core.request.mode',
			'defaultMethod'				=> 'core.request.method',
			'characterEncoding'			=> 'core.process.encoding',
			'decodeUTF8Input'			=> 'core.process.decode_utf8',
			'cleanBuffer'				=> 'core.process.clean_buffer',
			'exitAllowed'				=> 'core.process.exit_after',
			'scriptLoadTimeout'			=> 'core.process.load_timeout',
			'waitCursor'				=> 'core.process.show_cursor',
			'statusMessages'			=> 'core.process.show_status',
			'errorHandler'				=> 'core.error.handle',
			'logFile'					=> 'core.error.log_file',
			'debug'						=> 'core.debug.on',
			'verboseDebug'				=> 'core.debug.verbose',
			'debugOutputID'				=> 'core.debug.output_id',
			'javascript URI'			=> array('core.js.uri', 'core.js.lib_uri'),
			'javascript Dir'			=> 'core.js.dir',
			'deferScriptGeneration'		=> array('core.js.merge', 'core.js.minify'),
			'deferDirectory'			=> 'core.js.dir',
			'scriptDefferal'			=> 'core.js.options',
		);
	}

	/**
	 * Get the unique instance of the Xajax class
	 *
	 * @return object
	 */
	public static function getInstance()
	{
		static $xInstance = null;
		if(!$xInstance)
		{
			$xInstance = new Xajax();
		}
		return $xInstance;
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
			'core.version' => $this->getVersion(),
			'core.language' => 'en',
			'core.prefix.function' => 'xajax_',
			'core.prefix.class' => 'Xajax',
			'core.prefix.event' => 'xajax_event_',
			'core.request.uri' => '',
			'core.request.mode' => 'asynchronous',
			'core.request.method' => 'POST',	// W3C: Method is case sensitive
			'core.debug.on' => false,
			'core.debug.verbose' => false,
			'core.debug.output_id' => 0,
			'core.process.encoding' => 'utf-8',
			'core.process.load_timeout' => 2000,
			'core.process.decode_utf8' => false,
			'core.process.show_status' => false,
			'core.process.wait_cursor' => true,
			'core.process.exit_after' => true,
			'core.error.handle' => false,
			'core.error.log_file' => '',
			'core.process.clean_buffer' => false,
			'core.response.queue_size' => 0,
			'core.response.timeout' => 6000,
			'core.js.dir' => '',
			'core.js.minify' => true,
			'core.js.options' => '',
		));
		// Todo : check if this code should not be moved somewhere else
		/*if(XAJAX_DEFAULT_CHAR_ENCODING != 'utf-8')
		{
			$this->setOption('core.process.decode_utf8', true);
		}*/
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
				Xajax::USER_FUNCTION: a function declared at global scope.
				Xajax::CALLABLE_OBJECT: an object who's methods are to be registered.
				Xajax::BROWSER_EVENT: an event which will cause zero or more event handlers
					to be called.
				Xajax::EVENT_HANDLER: register an event handler function.
				
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

		if(self::PROCESSING_EVENT == $aArgs[0])
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
	public function addClassDir($sPath, $sNamespace = null, array $aExcluded = array())
	{
		return $this->xPluginManager->addClassDir($sPath, $sNamespace, $aExcluded);
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
		$this->setOption('core.js.lib_uri', $sJsLibURI);
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
		$this->setOption('core.js.merge', true);
		$this->setOption('core.js.dir', $sJsAppDir);
		$this->setOption('core.js.uri', $sJsAppURI);
		$this->setOption('core.js.minify', ($bMinifyJs));
	}

	/*
		Function: configureMany
		
		Set an array of configuration options.

		Parameters:
		
		$aOptions - (array): Associative array of configuration settings
	*/
	public function configureMany(array $aOptions)
	{
		foreach($aOptions as $sName => $xValue)
		{
			$this->configure($sName, $xValue);
		}
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
		// The config name must be mapped to the new option name
		if(!array_key_exists($sName, $this->aOptionMappings))
		{
			return;
		}
		$sName = $this->aOptionMappings[$sName];
		if(!is_array($sName))
		{
			$sName = array($sName);
		}
		foreach($sName as $name)
		{
			$this->setOption($name, $xValue);
		}
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
		// The config name must be mapped to the new option name
		if(!array_key_exists($sName, $this->aOptionMappings))
		{
			return null;
		}
		$sName = $this->aOptionMappings[$sName];
		return $this->getOption((is_array($sName) ? $sName[0] : $sName));
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
		Function errorHandler
	
		This function is registered with PHP's set_error_handler if the xajax
		error handling system is enabled.
	
		See <xajax->bUserErrorHandler>
	*/
	public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		$errorReporting = error_reporting();
		if (($errno & $errorReporting) == 0 || (defined('E_STRICT') && $errno == E_STRICT))
		{
			return;
		}

		$aErrTypes = array(
			E_NOTICE => 'NOTICE',
			E_WARNING => 'WARNING',
			E_USER_NOTICE => 'USER NOTICE',
			E_USER_WARNING => 'USER	WARNING',
			E_USER_ERROR => 'USER FATAL ERROR',
		);
		$sErrorType = (array_key_exists($errno, $aErrTypes) ? $aErrTypes[$errno] : 'UNKNOWN: ' . $errno);
		$this->sErrorMessage = $this->render('plugins/errors.txt.tpl', array(
			'sPrevMessage' => $this->sErrorMessage,
			'sErrorType' => $sErrorType,
			'sErrorMessage' => $errstr,
			'sErrorFile' => $errfile,
			'sErrorLine' => $errline,
		));
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
			if(($this->getOption('core.error.handle')))
			{
				$this->setErrorMessage('');
				set_error_handler(array($this, "errorHandler"));
			}
			
			$mResult = true;

			// handle beforeProcessing event
			if(isset($this->aProcessingEvents[self::PROCESSING_EVENT_BEFORE]))
			{
				$bEndRequest = false;
				$this->aProcessingEvents[self::PROCESSING_EVENT_BEFORE]->call(array(&$bEndRequest));
				$mResult = (false === $bEndRequest);
			}

			if(true === $mResult)
				$mResult = $this->xPluginManager->processRequest();

			if(true === $mResult)
			{
				if(($this->getOption('core.process.clean_buffer')))
				{
					$er = error_reporting(0);
					while (ob_get_level() > 0) ob_end_clean();
					error_reporting($er);
				}

				// handle afterProcessing event
				if(isset($this->aProcessingEvents[self::PROCESSING_EVENT_AFTER]))
				{
					$bEndRequest = false;

					$this->aProcessingEvents[self::PROCESSING_EVENT_AFTER]->call(array($bEndRequest));

					if($bEndRequest === true)
					{
						$this->xResponseManager->clear();
						$this->xResponseManager->append($aResult[1]);
					}
				}
			}
			else if(is_string($mResult))
			{
				if(($this->getOption('core.process.clean_buffer')))
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
				if(isset($this->aProcessingEvents[self::PROCESSING_EVENT_INVALID]))
					$this->aProcessingEvents[self::PROCESSING_EVENT_INVALID]->call();
				else
					$this->xResponseManager->debug($mResult);
			}

			if(($this->sErrorMessage) && ($this->getOption('core.error.handle')) &&
				$this->hasOption('core.error.log_file'))
			{
				if(($fH = @fopen($this->getOption('core.error.log_file'), "a")) != null)
				{
					fwrite($fH, $this->trans('errors.debug.ts-message', array(
						'timestamp' => strftime("%b %e %Y %I:%M:%S %p"),
						'message' => $this->sErrorMessage
					)));
					fclose($fH);
				}
				else
				{
					$this->xResponseManager->debug($this->trans('errors.debug.write-log', array(
						'file' => $this->getOption('core.error.log_file')
					)));
				}
				$this->xResponseManager->debug($this->trans('errors.debug.message', array(
					'message' => $this->sErrorMessage
				)));
			}

			$this->xResponseManager->send();

			if(($this->getOption('core.error.handle')))
			{
				restore_error_handler();
			}
			if(($this->getOption('core.process.exit_after')))
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
	public function getJavascript($bIncludeAssets = true)
	{
		if(!$this->hasOption('core.request.uri'))
		{
			$this->setOption('core.request.uri', URI::detect());
		}
		$sCode = '';
		if(($bIncludeAssets))
		{
			$sCode .= $this->xPluginManager->getCssInclude() . "\n" . $this->xPluginManager->getJsInclude() . "\n";
		}
		$sCode .= $this->xPluginManager->getClientScript();
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
