<?php

namespace Xajax\Plugin;

use Xajax\Xajax;
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
	use \Xajax\Utils\ContainerTrait;

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

		// Set response type to JSON
		$this->sResponseType = 'JSON';
	}

	/*
		Function: getInstance
		
		Implementation of the singleton pattern: returns the one and only instance of the 
		xajax plugin manager.
		
		Returns:
		
		object : a reference to the one and only instance of the plugin manager.
	*/
	public static function getInstance()
	{
		static $xInstance = null;
		if(!$xInstance)
		{
			$xInstance = new Manager();    
		}
		return $xInstance;
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
		$this->registerPlugin(new \Xajax\Request\Plugin\BrowserEvent(), 103);
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
	public function addClassDir($sDir, $sNamespace = null, array $aExcluded = array())
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
		$this->aClassDirs[] = array('path' => $sDir, 'namespace' => $sNamespace, 'excluded' => $aExcluded);
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
	protected function registerClassFromFile($xFile, $sDir, $sNamespace = null, array $aExcluded = array())
	{
		// Get the corresponding class path and name
		$sClassPath = trim(substr($xFile->getPath(), strlen($sDir)), DIRECTORY_SEPARATOR);
		$sClassPath = str_replace(array(DIRECTORY_SEPARATOR), array('.'), $sClassPath);
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
		if(($sNamespace))
		{
			$aOptions['*']['namespace'] = $sNamespace;
		}
		if(($sClassPath))
		{
			$aOptions['*']['classpath'] = $sClassPath;
		}
		// Filter excluded methods
		$aExcluded = array_filter($aExcluded, function($sName){return is_string($sName);});
		if(count($aExcluded) > 0)
		{
			$aOptions['*']['excluded'] = $aExcluded;
		}
		$this->register(array(Xajax::CALLABLE_OBJECT, $xCallableObject, $aOptions));
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
				$this->registerClassFromFile($xFile, $sClassDir['path'], $sClassDir['namespace'], $sClassDir['excluded']);
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
	public function registerClass($sClassName, array $aExcluded = array())
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
		$sClassFile = str_replace(array('.'), array(DIRECTORY_SEPARATOR), $sClassPath) . DIRECTORY_SEPARATOR . $sClassName . '.php';
		foreach($this->aClassDirs as $aClassDir)
		{
			$sNamespace = $aClassDir['namespace'];
			$nLen = strlen($sNamespace);
			// Check if the class belongs to the namespace
			if(($sNamespace) && substr($sClassPath, 0, $nLen) == str_replace(array('\\'), array('.'), $sNamespace))
			{
				$sClassFile = $aClassDir['path'] . DIRECTORY_SEPARATOR . substr($sClassFile, $nLen);
				if(is_file($sClassFile))
				{
					$this->registerClassFromFile(new \SplFileInfo($sClassFile), $aClassDir['path'], $sNamespace, $aExcluded);
					return true;
				}
			}
			else if(!($sNamespace) && is_file($aClassDir['path'] . DIRECTORY_SEPARATOR . $sClassFile))
			{
				$sClassFile = $aClassDir['path'] . DIRECTORY_SEPARATOR . $sClassFile;
				if(is_file($sClassFile))
				{
					$this->registerClassFromFile(new \SplFileInfo($sClassFile), $aClassDir['path'], $aExcluded);
					return true;
				}
			}
		}
		return false;
	}

	/*
		Function: getJsLibURI
		
		Return the URI of the Xajax javascript library files.
		
	*/
	public function getJsLibURI()
	{
		if(!$this->hasOption('js.lib.uri'))
		{
			return '//assets.lagdo-software.net/libs/xajax/js/latest/';
		}
		// Todo: check the validity of the URI
		return rtrim($this->getOption('js.lib.uri'), '/') . '/';
	}
	
	/*
		Function: canMergeJavascript
		
		Check if the javascript code generated by Xajax can be merged.
	*/
	public function canMergeJavascript()
	{
		// Check config options
		// - The js.app.merge option must be set to true
		// - The js.app.uri and js.app.dir options must be present
		if(!$this->getOption('js.app.merge') ||
			!$this->hasOption('js.app.uri') ||
			!$this->hasOption('js.app.dir'))
		{
			return false;
		}
		// Check dir access
		// - The js.app.dir must be writable
		$sJsAppDir = $this->getOption('js.app.dir');
		if(!is_dir($sJsAppDir) || !is_writable($sJsAppDir))
		{
			return false;
		}
		return true;
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
		if(($this->getOption('js.app.minify')))
		{
			return str_replace('.js', '.min.js', $sFilename);
		}
		return $sFilename;
	}

	private function setTemplateCacheDir()
	{
		if($this->hasOption('core.template.cache_dir'))
		{
			$this->setCacheDir($this->getOption('core.template.cache_dir'));
		}
	}

	/*
	 Function: getJsInclude
	
	 Returns the javascript header includes for response plugins.
	
	 Parameters:
	 */
	public function getJsInclude()
	{
		$sJsLibURI = $this->getJsLibURI();
		$sJsCoreUrl = $sJsLibURI . $this->_getScriptFilename('xajax.core.js');
		$sJsReadyUrl = $sJsLibURI . $this->_getScriptFilename('xajax.ready.js');
		$sJsDebugUrl = $sJsLibURI . $this->_getScriptFilename('xajax.debug.js');
		$sJsVerboseUrl = $sJsLibURI . $this->_getScriptFilename('xajax.verbose.js');
		$sJsLanguageUrl = $sJsLibURI . $this->_getScriptFilename('lang/xajax.' . $this->getOption('core.language') . '.js');

		// Add component files to the javascript file array;
		$aJsFiles = array($sJsCoreUrl, $sJsReadyUrl);
		if($this->getOption('core.debug.on'))
		{
			$aJsFiles[] = $sJsDebugUrl;
			$aJsFiles[] = $sJsLanguageUrl;
			if($this->getOption('core.debug.verbose'))
			{
				$aJsFiles[] = $sJsVerboseUrl;
			}
		}

		// Set the template engine cache dir
		$this->setTemplateCacheDir();
		$sCode = $this->render('plugins/includes.js.tpl', array(
			'sJsOptions' => $this->getOption('js.app.options'),
			'aUrls' => $aJsFiles,
		));
		foreach($this->aResponsePlugins as $xPlugin)
		{
			$sCode .= rtrim($xPlugin->getJsInclude(), " \n") . "\n";
		}
		return $sCode;
	}

	/*
	 Function: getCssInclude
	
	 Returns the CSS header includes for response plugins.
	
	 Parameters:
	 */
	public function getCssInclude()
	{
		// Set the template engine cache dir
		$this->setTemplateCacheDir();

		$sCode = '';
		foreach($this->aResponsePlugins as $xPlugin)
		{
			$sCode .= rtrim($xPlugin->getCssInclude(), " \n") . "\n";
		}
		return $sCode;
	}

	private function getOptionVars()
	{
		return array(
			'sResponseType' 			=> $this->sResponseType,
			'sVersion' 					=> $this->getOption('core.version'),
			'sLanguage' 				=> $this->getOption('core.language'),
			'bLanguage' 				=> $this->hasOption('core.language') ? true : false,
			'sRequestURI' 				=> $this->getOption('core.request.uri'),
			'sDefaultMode' 				=> $this->getOption('core.request.mode'),
			'sDefaultMethod' 			=> $this->getOption('core.request.method'),
			'nResponseQueueSize' 		=> $this->getOption('core.response.queue_size'),
			'sStatusMessages' 			=> $this->getOption('core.process.show_status') ? 'true' : 'false',
			'sWaitCursor' 				=> $this->getOption('core.process.show_cursor') ? 'true' : 'false',
			'nScriptLoadTimeout' 		=> $this->getOption('core.process.load_timeout'),
			'bDebug' 					=> $this->getOption('core.debug.on'),
			'bVerboseDebug' 			=> $this->getOption('core.debug.verbose'),
			'sDebugOutputID' 			=> $this->getOption('core.debug.output_id'),
			'sDefer' 					=> $this->getOption('js.app.options'),
		);
	}

	private function getConfigScript()
	{
		$aVars = $this->getOptionVars();
		return $this->render('plugins/config.js.tpl', $aVars);
	}

	private function getReadyScript()
	{
		// Print Xajax config vars
		$sJsLibURI = $this->getJsLibURI();
		$sJsCoreUrl = $sJsLibURI . $this->_getScriptFilename('xajax.core.js');
		$sJsDebugUrl = $sJsLibURI . $this->_getScriptFilename('xajax.debug.js');
		$sJsVerboseUrl = $sJsLibURI . $this->_getScriptFilename('xajax.verbose.js');
		$sJsLanguageUrl = $sJsLibURI . $this->_getScriptFilename('lang/xajax.' . $this->getOption('core.language') . '.js');

		$sJsCoreError = $this->trans('errors.component.load', array(
			'name' => 'xajax',
			'url' => $sJsCoreUrl,
		));
		$sJsDebugError = $this->trans('errors.component.load', array(
			'name' => 'xajax.debug',
			'url' => $sJsDebugUrl,
		));
		$sJsVerboseError = $this->trans('errors.component.load', array(
			'name' => 'xajax.debug.verbose',
			'url' => $sJsVerboseUrl,
		));
		$sJsLanguageError = $this->trans('errors.component.load', array(
			'name' => 'xajax.debug.lang',
			'url' => $sJsLanguageUrl,
		));

		$sPluginScript = '';
		foreach($this->aResponsePlugins as $xPlugin)
		{
			$sPluginScript .= "\n" . trim($xPlugin->getClientScript(), " \n");
		}

		$aVars = $this->getOptionVars();
		$aVars['sPluginScript'] = $sPluginScript;
		$aVars['sJsCoreError'] = $sJsCoreError;
		$aVars['sJsDebugError'] = $sJsDebugError;
		$aVars['sJsVerboseError'] = $sJsVerboseError;
		$aVars['sJsLanguageError'] = $sJsLanguageError;

		return $this->render('plugins/ready.js.tpl', $aVars);
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
		// Set the template engine cache dir
		$this->setTemplateCacheDir();

		// Get the config and plugins scripts
		$sScript = $this->getConfigScript() . "\n" . $this->getReadyScript() . "\n";
		foreach($this->aRequestPlugins as $xPlugin)
		{
			$sScript .= "\n" . trim($xPlugin->getClientScript(), " \n");
		}
		if($this->canMergeJavascript())
		{
			$sJsAppURI = rtrim($this->getOption('js.app.uri'), '/') . '/';
			$sJsAppDir = rtrim($this->getOption('js.app.dir'), '/') . '/';

			// The plugins scripts are written into the javascript app dir
			$sHash = $this->generateHash();
			$sOutFile = $sHash . '.js';
			$sMinFile = $sHash . '.min.js';
			if(!is_file($sJsAppDir . $sOutFile))
			{
				file_put_contents($sJsAppDir . $sOutFile, $sScript);
			}
			if(($this->getOption('js.app.minify')))
			{
				if(($this->minify($sJsAppDir . $sOutFile, $sJsAppDir . $sMinFile)))
				{
					$sOutFile = $sMinFile;
				}
			}

			// The returned code loads the generated javascript file
			$sScript = $this->render('plugins/include.js.tpl', array(
				'sJsOptions' => $this->getOption('js.app.options'),
				'sUrl' => $sJsAppURI . $sOutFile,
			));
		}
		else
		{
			// The plugins scripts are wrapped with javascript tags
			$sScript = $this->render('plugins/wrapper.js.tpl', array(
				'sJsOptions' => $this->getOption('js.app.options'),
				'sScript' => $sScript,
			));
		}
		
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
