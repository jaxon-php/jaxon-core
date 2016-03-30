<?php

namespace Xajax\Plugin;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RecursiveRegexIterator;

/*
	File: Manager.php

	Contains the xajax plugin manager.
	
	Title: xajax plugin manager
	
	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: Manager.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: Manager
*/
class Manager
{
	use \Xajax\Utils\TemplateTrait, \Xajax\Utils\MinifierTrait;

	/*
		Array: aPlugins
		All plugins, indexed by priorities
	*/
	private $aPlugins;
	
	/*
		Array: aRequestPlugins
		Request plugins, indexed by names
	*/
	private $aRequestPlugins;
	
	/*
		Array: aResponsePlugins
		Response plugins, indexed by names
	*/
	private $aResponsePlugins;
	
	/*
		Array: aClassDirs
		Directories where Xajax classes to be registered are found
	*/
	private $aClassDirs;
	
	/*
		Object: xAutoLoader
		The PHP class autoloader
	*/
	private $bAutoLoadEnabled;
	private $xAutoLoader;
	
	private $sJsLibURI;
	private $sDefer;

	private $bMergeJs;
	private $bMinifyJs;
	private $sJsAppURI;
	private $sJsAppDir;

	private $sRequestURI;
	private $sStatusMessages;
	private $sWaitCursor;
	private $sVersion;
	private $sDefaultMode;
	private $sDefaultMethod;
	private $bDebug;
	private $bVerboseDebug;
	private $nScriptLoadTimeout;
	private $sLanguage;
	private $nResponseQueueSize;
	private $sDebugOutputID;
	private $sResponseType;

	/*
		Object: xInstance
		The only instance of the plugin manager (Singleton)
	*/
	private static $xInstance = null;

	/*
		Function: getInstance
		
		Implementation of the singleton pattern: returns the one and only instance of the 
		xajax plugin manager.
		
		Returns:
		
		object : a reference to the one and only instance of the plugin manager.
	*/
	public static function getInstance()
	{
		if(!self::$xInstance)
		{
			self::$xInstance = new Manager();    
		}
		return self::$xInstance;
	}
	
	/*
		Function: __construct
		
		Construct and initialize the one and only Xajax plugin manager.
	*/
	private function __construct()
	{
		$this->aRequestPlugins = array();
		$this->aResponsePlugins = array();
		$this->aPlugins = array();

		$this->aClassDirs = array();

		$this->bAutoLoadEnabled = true;
		$this->xAutoLoader = null;

		// By default, the js files are loaded from this URI
		$this->sJsLibURI = '//assets.lagdo-software.net/libs/xajax/js/latest/';

		$this->bMergeJs = false;
		$this->bMinifyJs = false;
		$this->sJsAppURI = '';
		$this->sJsAppDir = '';

		$this->sDefer = '';
		$this->sRequestURI = '';
		$this->sStatusMessages = 'false';
		$this->sWaitCursor = 'true';
		$this->sVersion = 'unknown';
		$this->sDefaultMode = 'asynchronous';
		$this->sDefaultMethod = 'POST';	// W3C: Method is case sensitive
		$this->bDebug = false;
		$this->bVerboseDebug = false;
		$this->nScriptLoadTimeout = 2000;
		$this->sLanguage = 'en';
		$this->nResponseQueueSize = null;
		$this->sDebugOutputID = null;
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
		$this->bAutoLoadEnabled = true;
		$this->xAutoLoader = $xAutoLoader;
	}
	
	/**
	 * Disable the PHP class autoloader
	 *
	 * @return void
	 */
	public function disableAutoLoad()
	{
		$this->bAutoLoadEnabled = false;
		$this->xAutoLoader = null;
	}

	/**
	 * Return true if the PHP class autoloader is enabled
	 *
	 * @return bool
	 */
	public function autoLoadDisabled()
	{
		return (!$this->bAutoLoadEnabled);
	}

	/*
		Function: loadPlugins
		
		Loads the locally defined plugins.
		
		Parameters:
	*/
	public function loadPlugins()
	{
		$this->registerPlugin(new \Xajax\Request\Plugin\CallableObject(), 101);
		$this->registerPlugin(new \Xajax\Request\Plugin\UserFunction(), 102);
		$this->registerPlugin(new \Xajax\Request\Plugin\Event(), 103);
	}
	
	/*
		Function: setPluginPriority
		
		Inserts an entry into an array given the specified priority number. 
		If a plugin already exists with the given priority, the priority is
		automatically incremented until a free spot is found.  The plugin
		is then inserted into the empty spot in the array.
		
		Parameters:
		
		$aPlugins - (array): Plugins array
		$xPlugin - (object): A reference to an instance of a plugin.
		$nPriority - (number): The desired priority, used to order the plugins.
		
	*/
	private function setPluginPriority($xPlugin, $nPriority)
	{
		while (isset($this->aPlugins[$nPriority]))
		{
			$nPriority++;
		}
		$this->aPlugins[$nPriority] = $xPlugin;
		// Sort the array by ascending keys
		ksort($this->aPlugins);
	}
	
	/*
		Function: registerPlugin
		
		Registers a plugin.
		
		Parameters:
		
		xPlugin - (object):  A reference to an instance of a plugin.
		
		Note:
		Below is a table for priorities and their description:
		0 thru 999: Plugins that are part of or extensions to the xajax core
		1000 thru 8999: User created plugins, typically, these plugins don't care about order
		9000 thru 9999: Plugins that generally need to be last or near the end of the plugin list
	*/
	public function registerPlugin(Plugin $xPlugin, $nPriority = 1000)
	{
		if($xPlugin instanceof Request)
		{
			// The name of a request plugin is used as key in the plugin table
			$this->aRequestPlugins[$xPlugin->getName()] = $xPlugin;
		}
		else if( $xPlugin instanceof Response)
		{
			// The name of a response plugin is used as key in the plugin table
			$this->aResponsePlugins[$xPlugin->getName()] = $xPlugin;
		}
		else
		{
//SkipDebug
			throw new \Xajax\Exception\Error('errors.register.invalid', array('name' => get_class($xPlugin)));
//EndSkipDebug
		}

		$this->setPluginPriority($xPlugin, $nPriority);
	}

	private function generateHash()
	{
		$sHash = '';
		foreach($this->aRequestPlugins as $xPlugin)
		{
			$sHash .= $xPlugin->generateHash();
		}
		return md5($sHash);
	}

	/*
		Function: canProcessRequest
		
		Calls each of the request plugins and determines if the
		current request can be processed by one of them.  If no processor identifies
		the current request, then the request must be for the initial page load.
		
		See <xajax->canProcessRequest> for more information.
	*/
	public function canProcessRequest()
	{
		foreach($this->aRequestPlugins as $xPlugin)
		{
			if($xPlugin->canProcessRequest())
			{
				return true;
			}
		}
		return false;
	}

	/*
		Function: processRequest

		Calls each of the request plugins to request that they process the
		current request.  If the plugin processes the request, it will
		return true.
	*/
	public function processRequest()
	{
		foreach($this->aRequestPlugins as $xPlugin)
		{
			if($xPlugin->canProcessRequest())
			{
				return $xPlugin->processRequest();
			}
		}
		// Todo: throw an exception
		return false;
	}
	
	/*
		Function: setJavascriptURI
		
		Set the URI of the Xajax javascript library files.
		
		Parameters:
		
		sJsLibURI - (string):  The URI of the Xajax javascript library files.
	*/
	public function setJavascriptURI($sJsLibURI)
	{
		// Todo: check the validity of the URI
		$this->sJsLibURI = $sJsLibURI;
		if(substr($this->sJsLibURI, -1) != '/')
		{
			$this->sJsLibURI .= '/';
		}
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
		// Check dir access
		if(!is_dir($sJsAppDir) || !is_writable($sJsAppDir))
		{
			// Todo: throw an exception
			return;
		}
		// Todo: check the validity of the URI
		$this->sJsAppURI = $sJsAppURI;
		if(substr($this->sJsAppURI, -1) != '/')
		{
			$this->sJsAppURI .= '/';
		}
		$this->sJsAppDir = $sJsAppDir;
		if(substr($this->sJsAppDir, -1) != '/')
		{
			$this->sJsAppDir .= '/';
		}
		$this->bMergeJs = true;
		$this->bMinifyJs = ($bMinifyJs);
	}
	
	/*
		Function: configure
		
		Call each of the request plugins passing along the configuration
		setting specified.
		
		Parameters:
		
		sName - (string):  The name of the configuration setting to set.
		mValue - (mixed):  The value to be set.
	*/
	public function configure($sName, $mValue)
	{
		foreach($this->aPlugins as $xPlugin)
		{
			$xPlugin->configure($sName, $mValue);
		}

		switch($sName)
		{
		case "scriptDefferal":
			$this->sDefer = ($mValue === true ? "defer" : "");
			break;
		case "requestURI":
			$this->sRequestURI = $mValue;
			break;
		case "statusMessages":
			$this->sStatusMessages = ($mValue === true ? "true" : "false");
			break;
		case "waitCursor":
			$this->sWaitCursor = ($mValue === true ? "true" : "false");
			break;
		case "version":
			$this->sVersion = $mValue;
			break;
		case "defaultMode":
			if($mValue == "asynchronous" || $mValue == "synchronous")
				$this->sDefaultMode = $mValue;
			break;
		case "defaultMethod":
			if($mValue == "POST" || $mValue == "GET")	// W3C: Method is case sensitive
				$this->sDefaultMethod = $mValue;
			break;
		case "debug":
			if($mValue === true || $mValue === false)
				$this->bDebug = $mValue;
			break;
		case "verboseDebug":
			if($mValue === true || $mValue === false)
				$this->bVerboseDebug = $mValue;
			break;
		case "scriptLoadTimeout":
			$this->nScriptLoadTimeout = $mValue;
			break;
		case 'language':
			$this->sLanguage = $mValue;
			break;
		case 'responseQueueSize':
			$this->nResponseQueueSize = intval($mValue);
			break;
		case 'debugOutputID':
			$this->sDebugOutputID = $mValue;
			break;
		case 'responseType':
			$this->sResponseType = $mValue;
			break;
		default:
			break;
		}
	}
	
	/*
		Function: register
		
		Call each of the request plugins and give them the opportunity to 
		handle the registration of the specified function, event or callable object.
		
		Parameters:
		 $aArgs - (array) :
	*/
	public function register($aArgs)
	{
		foreach($this->aRequestPlugins as $xPlugin)
		{
			$mResult = $xPlugin->register($aArgs);
			if($mResult instanceof \Xajax\Request\Request || is_array($mResult) || $mResult === true)
			{
				return $mResult;
			}
		}
//SkipDebug
		throw new \Xajax\Exception\Error('errors.register.method', array('args' => print_r($aArgs, true)));
//EndSkipDebug
	}

	/**
	 * Add a path to the class directories
	 *
	 * @param string		$sDir			The path to the directory
	 * @param string|null	$sNamespace		The associated namespace
	 *
	 * @return boolean
	 */
	public function addClassDir($sDir, $sNamespace = null)
	{
		if(!is_dir(($sDir = trim($sDir))))
		{
			return false;
		}
		if(!($sNamespace = trim($sNamespace)))
		{
			$sNamespace = null;
		}
		if(($sNamespace))
		{
			$sNamespace = trim($sNamespace, '\\');
			// If there is an autoloader, register the dir with PSR4 autoloading
			if(($this->xAutoLoader))
			{
				$this->xAutoLoader->setPsr4($sNamespace . '\\', $sDir);
			}
		}
		else if(($this->xAutoLoader))
		{
			// If there is an autoloader, register the dir with classmap autoloading
			$itDir = new RecursiveDirectoryIterator($sDir);
			$itFile = new RecursiveIteratorIterator($itDir);
			// Iterate on dir content
			foreach($itFile as $xFile)
			{
				// skip everything except PHP files
				if(!$xFile->isFile() || $xFile->getExtension() != 'php')
				{
					continue;
				}
				$this->xAutoLoader->addClassMap(array($xFile->getBasename('.php') => $xFile->getPathname()));
			}
		}
		$this->aClassDirs[] = array('path' => $sDir, 'namespace' => $sNamespace);
		return true;
	}

	/**
	 * Register an instance of a given class
	 *
	 * @param object		$xFile			The PHP file containing the class
	 * @param string		$sDir			The path to the directory
	 * @param string|null	$sNamespace		The associated namespace
	 *
	 * @return void
	 */
	protected function registerClassFromFile($xFile, $sDir, $sNamespace = null)
	{
		// Get the corresponding class path and name
		$sClassPath = trim(substr($xFile->getPath(), strlen($sDir)), '/');
		$sClassPath = str_replace(array('/'), array('.'), $sClassPath);
		$sClassName = $xFile->getBasename('.php');
		if(($sNamespace))
		{
			$sNamespace = trim($sNamespace, '\\');
			$sClassPath = str_replace(array('\\'), array('.'), $sNamespace) . '.' . $sClassPath;
			$sClassPath = rtrim($sClassPath, '.');
			$sClassName = '\\' . str_replace(array('.'), array('\\'), $sClassPath) . '\\' . $sClassName;
		}
		// Require the file only if there is no autoloader
		if(!$this->autoLoadDisabled())
		{
			require_once($xFile->getPathname());
		}
		// Create and register an instance of the class
		$xCallableObject = new $sClassName;
		$aOptions = array('*' => array());
		if(($sClassPath))
		{
			$aOptions['*']['classpath'] = $sClassPath;
		}
		$this->register(array(XAJAX_CALLABLE_OBJECT, $xCallableObject, $aOptions));
	}

	/**
	 * Register callable objects from all class directories
	 *
	 * @return void
	 */
	public function registerClasses()
	{
		foreach($this->aClassDirs as $sClassDir)
		{
			$itDir = new RecursiveDirectoryIterator($sClassDir['path']);
			$itFile = new RecursiveIteratorIterator($itDir);
			// Iterate on dir content
			foreach($itFile as $xFile)
			{
				// skip everything except PHP files
				if(!$xFile->isFile() || $xFile->getExtension() != 'php')
				{
					continue;
				}
				$this->registerClassFromFile($xFile, $sClassDir['path'], $sClassDir['namespace']);
			}
		}
	}

	/**
	 * Register an instance of a given class
	 *
	 * @param string|null	$sClassName		The name of the class to register
	 *
	 * @return bool
	 */
	public function registerClass($sClassName)
	{
		if(!($sClassName = trim($sClassName)))
		{
			return false;
		}
		// Replace / and \ with dots, and trim the string
		$sClassName = trim(str_replace(array('\\', '/'), array('.', '.'), $sClassName), '.');
		$sClassPath = '';
		if(($nLastDotPosition = strrpos($sClassName, '.')) !== false)
		{
			$sClassPath = substr($sClassName, 0, $nLastDotPosition);
			$sClassName = substr($sClassName, $nLastDotPosition + 1);
		}
		$sClassFile = str_replace(array('.'), array('/'), $sClassPath) . '/' . $sClassName . '.php';
		foreach($this->aClassDirs as $aClassDir)
		{
			$sNamespace = $aClassDir['namespace'];
			$nLen = strlen($sNamespace);
			// Check if the class belongs to the namespace
			if(($sNamespace) && substr($sClassPath, 0, $nLen) == str_replace(array('\\'), array('.'), $sNamespace))
			{
				$sClassFile = $aClassDir['path'] . '/' . substr($sClassFile, $nLen);
				if(is_file($sClassFile))
				{
					$this->registerClassFromFile(new \SplFileInfo($sClassFile), $aClassDir['path'], $sNamespace);
					return true;
				}
			}
			else if(!($sNamespace) && is_file($aClassDir['path'] . '/' . $sClassFile))
			{
				$sClassFile = $aClassDir['path'] . '/' . $sClassFile;
				if(is_file($sClassFile))
				{
					$this->registerClassFromFile(new \SplFileInfo($sClassFile), $aClassDir['path']);
					return true;
				}
			}
		}
		return false;
	}

	/*
		Function: _getScriptFilename

		Returns the name of the script file, based on the current settings.

		sFilename - (string):  The base filename.

		Returns:

		string - The filename as it should be specified in the script tags
		on the browser.
	*/
	private function _getScriptFilename($sFilename)
	{
		if($this->bMinifyJs)
		{
			return str_replace('.js', '.min.js', $sFilename);
		}
		return $sFilename;
	}

	/*
	 Function: getJsInclude
	
	 Returns the javascript header includes for response plugins.
	
	 Parameters:
	 */
	public function getJsInclude()
	{
		$sJsCoreUrl = $this->sJsLibURI . $this->_getScriptFilename('xajax.core.js');
		$sJsDebugUrl = $this->sJsLibURI . $this->_getScriptFilename('xajax.debug.js');
		$sJsVerboseUrl = $this->sJsLibURI . $this->_getScriptFilename('xajax.verbose.js');
		$sJsLanguageUrl = $this->sJsLibURI . $this->_getScriptFilename('lang/xajax.' . $this->sLanguage . '.js');

		// Add component files to the javascript file array;
		$aJsFiles = array($sJsCoreUrl);
		if($this->bDebug)
		{
			$this->aJsFiles[] = $sJsDebugUrl;
			if($this->bVerboseDebug)
			{
				$this->aJsFiles[] = $sJsVerboseUrl;
			}
			if(($this->sLanguage))
			{
				$this->aJsFiles[] = $sJsLanguageUrl;
			}
		}

		$code = $this->render('plugins/includes.js.tpl', array(
			'sDefer' => $this->sDefer,
			'aUrls' => $aJsFiles,
		));
		foreach($this->aResponsePlugins as $xPlugin)
		{
			$code .= rtrim($xPlugin->getJsInclude(), " \n") . "\n";
		}
		return $code;
	}
	
	/*
	 Function: getCssInclude
	
	 Returns the CSS header includes for response plugins.
	
	 Parameters:
	 */
	public function getCssInclude()
	{
		$code = '';
		foreach($this->aResponsePlugins as $xPlugin)
		{
			$code .= rtrim($xPlugin->getCssInclude(), " \n") . "\n";
		}
		return $code;
	}

	private function getConfigScript()
	{
		// Print Xajax config vars
		$templateVars = array();
		$varNames = array('sDefer', 'sRequestURI', 'sStatusMessages', 'sWaitCursor', 'sVersion',
			'sDefaultMode', 'sDefaultMethod', 'sJsLibURI', 'sResponseType', 'nResponseQueueSize',
			'bDebug', 'bVerboseDebug', 'sDebugOutputID', 'nScriptLoadTimeout', 'sLanguage');
		foreach($varNames as $templateVar)
		{
			$templateVars[$templateVar] = $this->$templateVar;
		}
		$templateVars['bLanguage'] = ($this->sLanguage) ? true : false;

		$sJsCoreUrl = $this->sJsLibURI . $this->_getScriptFilename('xajax.core.js');
		$sJsDebugUrl = $this->sJsLibURI . $this->_getScriptFilename('xajax.debug.js');
		$sJsVerboseUrl = $this->sJsLibURI . $this->_getScriptFilename('xajax.verbose.js');
		$sJsLanguageUrl = $this->sJsLibURI . $this->_getScriptFilename('lang/xajax.' . $this->sLanguage . '.js');

		$sJsCoreError = xajax_trans('errors.component.load', array(
			'name' => 'xajax',
			'url' => $sJsCoreUrl,
		));
		$sJsDebugError = xajax_trans('errors.component.load', array(
			'name' => 'xajax.debug',
			'url' => $sJsDebugUrl,
		));
		$sJsVerboseError = xajax_trans('errors.component.load', array(
			'name' => 'xajax.debug.verbose',
			'url' => $sJsVerboseUrl,
		));
		$sJsLanguageError = xajax_trans('errors.component.load', array(
			'name' => 'xajax.debug.lang',
			'url' => $sJsLanguageUrl,
		));

		$templateVars['sJsCoreError'] = $sJsCoreError;
		$templateVars['sJsDebugError'] = $sJsDebugError;
		$templateVars['sJsVerboseError'] = $sJsVerboseError;
		$templateVars['sJsLanguageError'] = $sJsLanguageError;
	
		return $this->render('plugins/config.js.tpl', $templateVars);
	}
	
	private function getPluginScript()
	{
		$code = '';
		foreach($this->aPlugins as $xPlugin)
		{
			$code .= $xPlugin->getClientScript();
		}
		return $code;
	}

	/*
		Function: getClientScript
		
		Call each of the request and response plugins giving them the
		opportunity to output some javascript to the page being generated.  This
		is called only when the page is being loaded initially.  This is not 
		called when processing a request.
	*/
	public function getClientScript()
	{
		// Get the config and plugins scripts
		$sScript = $this->getConfigScript() . $this->getPluginScript();
		if(($this->bMergeJs))
		{
			// The plugins scripts are written into the javascript app dir
			$sHash = $this->generateHash();
			if(($this->bMinifyJs))
			{
				$sOutFile = $sHash . '.min.js';
				$sScript = $this->minify($sScript);
			}
			else
			{
				$sOutFile = $sHash . '.js';
			}

			if(!is_dir($this->sJsAppDir))
			{
				@mkdir($this->sJsAppDir);
			}

			if(!is_file($this->sJsAppDir . $sOutFile))
			{
				file_put_contents($this->sJsAppDir . $sOutFile, $sScript);
			}

			// The returned code loads the generated javascript file
			$sScript = $this->render('plugins/include.js.tpl', array(
				'sDefer' => $this->sDefer,
				'sUrl' => $this->sJsAppURI . $sOutFile,
			));
		}
		else
		{
			// The plugins scripts are wrapped with javascript tags
			$sScript = $this->render('plugins/wrapper.js.tpl', array(
				'sDefer' => $this->sDefer,
				'sScript' => $sScript,
			));
		}

		// Get the code to load the javascript library files
		/*$sScript .= $this->render('plugins/includes.js.tpl', array(
			'sDefer' => $this->sDefer,
			'aUrls' => $this->aJsFiles,
		));*/
		
		return $sScript;
	}

	/*
		Function: getResponsePlugin
		
		Locate the specified response plugin by name and return
		a reference to it if one exists.
		
		Parameters:
			$sName - (string): Name of the plugin.
			
		Returns:
			mixed : Returns plugin or null if not found.
	*/
	public function getResponsePlugin($sName)
	{
		if(array_key_exists($sName, $this->aResponsePlugins))
		{
			return $this->aResponsePlugins[$sName];
		}
		return null;
	}

	/*
		Function: getRequestPlugin
		
		Locate the specified response plugin by name and return
		a reference to it if one exists.
		
		Parameters:
			$sName - (string): Name of the plugin.
			
		Returns:
			mixed : Returns plugin or null if not found.
	*/
	public function getRequestPlugin($sName)
	{
		if(array_key_exists($sName, $this->aRequestPlugins))
		{
			return $this->aRequestPlugins[$sName];
		}
		return null;
	}
}
