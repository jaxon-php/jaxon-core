<?php

namespace Xajax\Utils;

class Config
{
	private $aOptions;

	public function __construct()
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

	/**
	 * Check the presence of a config option
	 *
	 * @param string		$sName			The option name
	 *
	 * @return bool		True if the option exists, and false if not
	 */
	public function hasOption($sName)
    {
    	return array_key_exists($sName, $this->aOptions);
	}
}
