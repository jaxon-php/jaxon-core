<?php

namespace Xajax\Plugin;

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
	use \Xajax\Template\EngineTrait;

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
		Function: Manager
		
		Construct and initialize the one and only xajax plugin manager.
	*/

	private $sJsURI;
	private $sJsDir;
	public  $aJsFiles = array();
	private $sDefer;
	private $sDeferDir;
	private $sRequestURI;
	private $sStatusMessages;
	private $sWaitCursor;
	private $sVersion;
	private $sDefaultMode;
	private $sDefaultMethod;
	private $bDebug;
	private $bVerboseDebug;
	private $nScriptLoadTimeout;
	private $bUseUncompressedScripts;
	private $bDeferScriptGeneration;
	private $sLanguage;
	private $nResponseQueueSize;
	private $sDebugOutputID;
	private $sResponseType;

	private function __construct()
	{
		$this->aRequestPlugins = array();
		$this->aResponsePlugins = array();
		
		$this->aPlugins = array();

		$this->sJsURI = '';
		$this->sJsDir = dirname(dirname(__FILE__)) . '/xajax/js';
		$this->aJsFiles = array();
		$this->sDefer = '';
		$this->sDeferDir = 'deferred';
		$this->sRequestURI = '';
		$this->sStatusMessages = 'false';
		$this->sWaitCursor = 'true';
		$this->sVersion = 'unknown';
		$this->sDefaultMode = 'asynchronous';
		$this->sDefaultMethod = 'POST';	// W3C: Method is case sensitive
		$this->bDebug = false;
		$this->bVerboseDebug = false;
		$this->nScriptLoadTimeout = 2000;
		$this->bUseUncompressedScripts = false;
		$this->bDeferScriptGeneration = false;
		$this->sLanguage = null;
		$this->nResponseQueueSize = null;
		$this->sDebugOutputID = null;
	}
	
	/*
		Function: getInstance
		
		Implementation of the singleton pattern: returns the one and only instance of the 
		xajax plugin manager.
		
		Returns:
		
		object : a reference to the one and only instance of the
			plugin manager.
	*/
	public static function getInstance()
	{
		static $obj;
		if(!$obj)
		{
			$obj = new Manager();    
		}
		return $obj;
	}
	
	/*
		Function: loadPlugins
		
		Loads the locally defined plugins.
		
		Parameters:
	*/
	public function loadPlugins()
	{
		$this->registerPlugin(new Request\CallableObject(), 101);
		$this->registerPlugin(new Request\UserFunction(), 102);
		$this->registerPlugin(new Request\Event(), 103);
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
		
		objPlugin - (object):  A reference to an instance of a plugin.
		
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
			$this->aRequestPlugins[/*$xPlugin->getName()*/] = $xPlugin;
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
			$mResult = $xPlugin->canProcessRequest();
			if($mResult === true || is_string($mResult))
			{
				return $mResult;
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
			$mResult = $xPlugin->processRequest();
			if($mResult === true || is_string($mResult))
			{
				return $mResult;
			}
		}
		return false;
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
		case 'javascript URI':
			$this->sJsURI = $mValue;
			break;
		case 'javascript Dir':
			$this->sJsDir = $mValue;
			break;
		case "javascript files":
			$this->aJsFiles = array_merge($this->aJsFiles, $mValue);
			break;
		case "scriptDefferal":
			$this->sDefer = ($mValue === true ? "defer" : "");
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
		case "useUncompressedScripts":
			if($mValue === true || $mValue === false)
				$this->bUseUncompressedScripts = $mValue;
			break;
		case 'deferScriptGeneration':
			if($mValue === true || $mValue === false)
				$this->bDeferScriptGeneration = $mValue;
			else if($mValue == 'deferred')
				$this->bDeferScriptGeneration = true;
			break;
		case 'deferDirectory':
			$this->sDeferDir = $mValue;
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
		if(!$this->bUseUncompressedScripts)
		{
			return str_replace('.js', '.min.js', $sFilename);
		}
		return $sFilename;
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

		$sJsURI = $this->sJsURI;

		$aJsFiles = $this->aJsFiles;

		if(substr($sJsURI, -1) == '/')
			$sJsURI = substr($sJsURI, 0, -1);

		$aJsFiles[] = array($this->_getScriptFilename('xajax.core.js'), 'xajax');

		if($this->bDebug)
		{
			$aJsFiles[] = array($this->_getScriptFilename('xajax.debug.js'), 'xajax.debug');
		}
		if($this->bVerboseDebug)
		{
			$aJsFiles[] = array($this->_getScriptFilename('xajax.verbose.js'), 'xajax.debug.verbose');
		}
		if(($this->sLanguage))
		{
			$aJsFiles[] = array($this->_getScriptFilename('lang/xajax.' . $this->sLanguage . '.js'), 'xajax');
		}

		// Print Xajax config vars
		$templateVars = array();
		$varNames = array('sDefer', 'sRequestURI', 'sStatusMessages', 'sWaitCursor', 'sVersion',
			'sDefaultMode', 'sDefaultMethod', 'sJsURI', 'sResponseType', 'nResponseQueueSize',
			'bDebug', 'sDebugOutputID', 'nScriptLoadTimeout');
		foreach($varNames as $templateVar)
		{
			$templateVars[$templateVar] = $this->$templateVar;
		}

		$sConfigScript = $this->render('plugins/config.js.tpl', $templateVars);

		// Get the code to check if the library components are loaded
		if($this->nScriptLoadTimeout > 0)
		{
			foreach($aJsFiles as $aJsFile)
			{
				$sConfigScript .= $this->render('plugins/component.js.tpl', array(
					'sDefer' => $this->sDefer,
					'nScriptLoadTimeout' => $this->nScriptLoadTimeout,
					'sFile' => $aJsFile[1],
					'sUrl' => $sJsURI . '/' . $aJsFile[0],
				));
			}
		}

		// Get the code to load the javascript library files
		$sFileScript = '';
		foreach($aJsFiles as $aJsFile)
		{
			$sFileScript .= $this->render('plugins/include.js.tpl', array(
				'sDefer' => $this->sDefer,
				'sUrl' => $sJsURI . '/' . $aJsFile[0],
			));
		}
		
		// Get the plugins scripts
		$sPluginScript = $this->getPluginScripts();

		if(($this->bDeferScriptGeneration))
		{
			// The plugins scripts are written into the deferred javascript file
			$sHash = $this->generateHash();
			$sOutFile = $sHash . '.js';
			$sOutPath = $this->sJsDir . '/' . $this->sDeferDir . '/';
			if(!is_file($sOutPath . $sOutFile) )
			{
				// The code compression is not yet implemented
				// require_once(dirname(__FILE__) . '/xajaxCompress.php');
				// $sPluginScript = xajaxCompressFile( $sPluginScript );

				file_put_contents($sOutPath . $sOutFile, $sPluginScript);
			}

			// The returned code loads the deferred javascript file
			$sPluginScript = $this->render('plugins/include.js.tpl', array(
				'sDefer' => $this->sDefer,
				'sUrl' => $sJsURI . '/' . $this->sDeferDir . '/' . $sOutFile,
			));
		}
		else
		{
			// The plugins scripts are wrapped with javascript tags
			$sPluginScript = $this->render('plugins/plugins.js.tpl', array(
				'sDefer' => $this->sDefer,
				'sScript' => $sPluginScript,
			));
		}

		return $sConfigScript . $sFileScript . $sPluginScript;
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

	private function getPluginScripts()
	{
		$code = '';
		foreach($this->aPlugins as $xPlugin)
		{
			$code .= $xPlugin->getClientScript();
		}
		return $code;
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
