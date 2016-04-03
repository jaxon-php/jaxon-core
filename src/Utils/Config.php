<?php

namespace Xajax\Utils;

class Config
{
	/*
		Array: aOptions
		Config options
	*/
	private $aOptions;
	
	/*
		Object: xInstance
		The only instance of the Config (Singleton)
	*/
	private static $xInstance = null;

	/*
		Function: getInstance
		
		Implementation of the singleton pattern: returns the one and only instance of the Config object
		
		Returns:
		
		object : a reference to the Config object.
	*/
	public static function getInstance()
	{
		if(!self::$xInstance)
		{
			self::$xInstance = new Config();    
		}
		return self::$xInstance;
	}

	/*
		Function: __construct
		
		Construct and initialize the one and only Xajax plugin manager.
	*/
	private function __construct()
	{
		$this->aOptions = array();
    }

	/**
	 * Set the value of a config option
	 *
	 * @param string		$sName			The option name
	 * @param mixed			$sValue			The option value
	 *
	 * @return void
	 */
    public function setOption($sName, $sValue)
    {
    	$this->aOptions[$sName] = $sValue;
	}

	/**
	 * Set the values of an array of config options
	 *
	 * @param array			$aOptions		The config options
	 *
	 * @return void
	 */
    public function setOptions(array $aOptions)
    {
    	$this->aOptions = array_merge($this->aOptions, $aOptions);
	}

	/**
	 * Get the value of a config option
	 *
	 * @param string		$sName			The option name
	 *
	 * @return mixed		The option value, or null if the option is unknown
	 */
	public function getOption($sName)
    {
    	return (array_key_exists($sName, $this->aOptions) ? $this->aOptions[$sName] : null);
	}
}
