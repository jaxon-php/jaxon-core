<?php

/**
 * Xajax.php - Xajax class
 *
 * The Xajax class uses a modular plug-in system to facilitate the processing
 * of special Ajax requests made by a PHP page.
 * It generates Javascript that the page must include in order to make requests.
 * It handles the output of response commands (see <Xajax\Response\Response>).
 * Many flags and settings can be adjusted to effect the behavior of the Xajax class
 * as well as the client-side javascript.
 *
 * @package xajax-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax;

use Xajax\Plugin\Manager as PluginManager;
use Xajax\Request\Manager as RequestManager;
use Xajax\Response\Manager as ResponseManager;

use Xajax\Utils\URI;

class Xajax extends Base
{
	use \Xajax\Utils\ContainerTrait;

	/**
	 * Mappings the previous config options to the current ones, so the library can still accept them
	 *
	 * @var array
	 */
	private $aOptionMappings;

	/**
	 * Processing event handlers that have been assigned during this run of the script
	 *
	 * @var array
	 */
	private $aProcessingEvents;

	/**
	 * A reference to the global <\Xajax\Plugin\Manager>
	 *
	 * @var \Xajax\Plugin\Manager
	 */
	private $xPluginManager;
	
	/**
	 * A reference to the global <\Xajax\Request\Manager>
	 *
	 * @var \Xajax\Request\Manager
	 */
	private $xRequestManager;
	
	/**
	 * A reference to the global <\Xajax\Response\Manager>
	 *
	 * @var \Xajax\Response\Manager
	 */
	private $xResponseManager;

	/**
	 * The challenge response sent by the client in the HTTP request
	 *
	 * @var string
	 */
	private $challengeResponse;
	
	/**
	 * A reference to the global <\Xajax\Response\Response>
	 *
	 * @var \Xajax\Response\Response
	 */
	protected static $gxResponse = null;

	/**
	 * The error message generated when the Xajax error handling system is enabled
	 *
	 * @var unknown
	 */
	private $sErrorMessage = '';

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
			'javascript URI'			=> array('js.app.uri', 'js.lib.uri'),
			'javascript Dir'			=> 'js.app.dir',
			'deferScriptGeneration'		=> array('js.app.export', 'js.app.minify'),
			'deferDirectory'			=> 'js.app.dir',
			'scriptDefferal'			=> 'js.app.options',
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
			'js.app.dir' => '',
			'js.app.minify' => true,
			'js.app.options' => '',
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

	/**
	 * Return the <Xajax\Response\Response> object preconfigured with the encoding and entity
	 * settings from this instance of <Xajax\Xajax>
	 *
	 * This is used for singleton-pattern response development.
	 *
	 * @return Xajax\Response\Response
	 *
	 * @see The <Xajax\Response\Manager> class
	 */
	public static function getGlobalResponse()
	{
		if(!self::$gxResponse)
		{
			self::$gxResponse = new Response\Response();
		}
		return self::$gxResponse;
	}

	/**
	 * The current Xajax version
	 *
	 * @return string
	 */
	public static function getVersion()
	{
		return 'Xajax 0.7 alpha 1';
	}

	/**
	 * Register request handlers, including functions, callable objects and events.
	 *
	 * New plugins can be added that support additional registration methods and request processors.
	 * 
	 *
	 * @param string	$sType			The type of request handler being registered
	 *        Options include:
	 *        - Xajax::USER_FUNCTION: a function declared at global scope
	 *        - Xajax::CALLABLE_OBJECT: an object who's methods are to be registered
	 *        - Xajax::BROWSER_EVENT: an event which will cause zero or more event handlers to be called
	 *        - Xajax::EVENT_HANDLER: register an event handler function.
	 * @param mixed		$sFunction | $objObject | $sEvent
	 *        When registering a function, this is the name of the function
	 *        When registering a callable object, this is the object being registered
	 *        When registering an event or event handler, this is the name of the event
	 * @param midex		$sIncludeFile | $aCallOptions | $sEventHandler
	 *        When registering a function, this is the (optional) include file
	 *        When registering a callable object, this is an (optional) array
	 *             of call options for the functions being registered
	 *        When registering an event handler, this is the name of the function
	 *
	 * @return mixed
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
	 * Register a callable object from one of the class directories
	 *
	 * The class name can be dot, slash or anti-slash separated.
	 * If the $bGetObject parameter is set to true, the registered instance of the class is returned.
	 * 
	 * @param string		$sClassName		The name of the class to register
	 * @param boolean		$bGetObject		Return the registered instance of the class
	 *
	 * @return void
	 */
	public function registerClass($sClassName, $bGetObject = false)
	{
		$this->xPluginManager->registerClass($sClassName);
		return (($bGetObject) ? $this->xPluginManager->getRegisteredObject($sClassName) : null);
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
		$this->setOption('js.lib.uri', $sJsLibURI);
	}
	
	/**
	 * Export and minify the javascript code generated by Xajax
	 *
	 * @param string		$sJsAppDir			The dir where the generated file will be located
	 * @param string		$sJsAppURI			The URI where the generated file will be located
	 * @param boolean		$bMinifyJs			Shall the generated file also be minified
	 *
	 * @return void
	 */
	public function exportJavascript($sJsAppDir, $sJsAppURI, $bMinifyJs = true)
	{
		$this->setOption('js.app.export', true);
		$this->setOption('js.app.dir', $sJsAppDir);
		$this->setOption('js.app.uri', $sJsAppURI);
		$this->setOption('js.app.minify', ($bMinifyJs));
	}

	/**
	 * Set an array of configuration options
	 *
	 * This function is deprecated, and will be removed in a future version. Use <setOptions> instead.
	 *
	 * @param array 		$aOptions			Associative array of configuration settings
	 *
	 * @return void
	 */
	public function configureMany(array $aOptions)
	{
		foreach($aOptions as $sName => $xValue)
		{
			$this->configure($sName, $xValue);
		}
	}
	
	/**
	 * Set a configuration option
	 *
	 * This function is deprecated, and will be removed in a future version. Use <setOption> instead.
	 *
	 * @param string 		$sName				The name of the configuration setting
	 * @param mixed			$xValue				The value of the setting
	 *
	 * @return void
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

	/**
	 * Get the current value of a configuration setting
	 *
	 * This function is deprecated, and will be removed in a future version. Use <getOption> instead.
	 *
	 * @param array 		$sName				The name of the configuration setting
	 *
	 * @return mixed
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

	/**
	 * Determine if a call is a xajax request or a page load request
	 *
	 * @return boolean
	 */
	public function canProcessRequest()
	{
		return $this->xPluginManager->canProcessRequest();
	}

	/**
	 * Ensure that an active session is available
	 *
	 * Primarily used for storing challenge / response codes.
	 *
	 * @return boolean
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

	/**
	 * Read the challenge data from the session
	 *
	 * @param string		$sessionKey			The session key
	 *
	 * @return mixed
	 */
	private function loadChallenges($sessionKey)
	{
		$challenges = array();

		if(isset($_SESSION[$sessionKey]))
			$challenges = $_SESSION[$sessionKey];

		return $challenges;
	}

	/**
	 * Save the challenge data in the session
	 *
	 * @param string		$sessionKey			The session key
	 * @param mixed			$challenges			The challenge data
	 *
	 * @return void
	 */
	private function saveChallenges($sessionKey, $challenges)
	{
		if(count($challenges) > 10)
			array_shift($challenges);

		$_SESSION[$sessionKey] = $challenges;
	}

	/**
	 * Make the challenge data
	 *
	 * @param string		$algo				The algorithm to use
	 * @param integer		$value				The value to hash
	 *
	 * @return string
	 */
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

	/**
	 * Introduce a challenge and response cycle into the request response process
	 *
	 * Sessions must be enabled to use this feature.
	 *
	 * @param string		$algo				The algorithm to use
	 * @param integer		$value				The value to hash
	 *
	 * @return void
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

	/**
	 * This function is registered with PHP's set_error_handler if the xajax error handling system is enabled
	 *
	 * @return void
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

	/**
	 * If this is a xajax request, call the requested PHP function, build the response and send it back to the browser
	 *
	 * This is the main server side engine for xajax.
	 * It handles all the incoming requests, including the firing of events and handling of the response.
	 * If your RequestURI is the same as your web page, then this function should be called before ANY
	 * headers or HTML is output from your script.
	 * 
	 * This function may exit after the request is processed, if the 'core.exit_after' option is set to true.
	 *
	 * @return void
	 * 
	 * @see <Xajax\Xajax->canProcessRequest>
	 */
	public function processRequest()
	{
		if(isset($_SERVER['HTTP_CHALLENGE_RESPONSE']))
			$this->challengeResponse = $_SERVER['HTTP_CHALLENGE_RESPONSE'];

		// Check to see if headers have already been sent out, in which case we can't do our job
		if(headers_sent($filename, $linenumber))
		{
			echo $this->trans('errors.output.already-sent', array(
				'location' => $filename . ':' . $linenumber
			)), "\n", $this->trans('errors.output.advice');
			exit();
		}

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

	/**
	 * Returns the Xajax Javascript header and wrapper code to be printed into the page
	 *
	 * The javascript code returned by this function is dependent on the plugins
	 * that are included and the functions and classes that are registered.
	 *
	 * @param boolean		$bIncludeJs			Also get the JS files
	 * @param boolean		$bIncludeCss		Also get the CSS files
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
			$sCode .= $this->xPluginManager->getCss() . "\n";
		}
		if(($bIncludeJs))
		{
			$sCode .= $this->xPluginManager->getJs() . "\n";
		}
		$sCode .= $this->xPluginManager->getScript();
		return $sCode;
	}

	/**
	 * Print the xajax Javascript header and wrapper code into your page
	 *
	 * The javascript code returned by this function is dependent on the plugins
	 * that are included and the functions and classes that are registered.
	 *
	 * @param boolean		$bIncludeJs			Also print the JS files
	 * @param boolean		$bIncludeCss		Also print the CSS files
	 *
	 * @return void
	 */
	public function printScript($bIncludeJs = false, $bIncludeCss = false)
	{
		print $this->getJavascript($bIncludeJs, $bIncludeCss);
	}

	/**
	 * Return the javascript header code and file includes
	 *
	 * @return string
	 */
	public function getJs()
	{
		return $this->xPluginManager->getJs();
	}

	/**
	 * Return the CSS header code and file includes
	 *
	 * @return string
	 */
	public function getCss()
	{
		return $this->xPluginManager->getCss();
	}

	/**
	 * Return a registered response plugin
	 *
	 * Pass the plugin name as the first argument and the plugin object will be returned.
	 * You can then access the methods of the plugin directly.
	 *
	 * @param string		$sName				The plugin name
	 *
	 * @return \Xajax\Plugin\Response
	 */
	public function plugin($sName)
	{
		$xPlugin = $this->xPluginManager->getResponsePlugin($sName);
		if(!$xPlugin)
		{
			return null;
		}
		$xPlugin->setResponse($this);
		return $xPlugin;
	}
}
