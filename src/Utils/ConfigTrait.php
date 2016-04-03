<?php

namespace Xajax\Utils;

trait ConfigTrait
{
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
		return Config::getInstance()->setOption($sName, $sValue);
	}
	
	/**
	 * Set the values of an array of config options
	 *
	 * @param array			$aOptions		The config options
	 *
	 * @return void
	 */
    public function setOptions($aOptions)
    {
    	return Config::getInstance()->setOptions($aOptions);
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
		return Config::getInstance()->getOption($sName);
	}
}
