<?php

/**
 * ContainerTrait.php - Trait for Utils classes
 *
 * Make functions of the utils classes available to Xajax classes.
 *
 * @package xajax-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax\Utils;

trait ContainerTrait
{
	/**
	 * Set the value of a config option
	 *
	 * @param string		$sName				The option name
	 * @param mixed			$sValue				The option value
	 *
	 * @return void
	 */
	public function setOption($sName, $sValue)
	{
		return Container::getInstance()->getConfig()->setOption($sName, $sValue);
	}
	
	/**
	 * Set the values of an array of config options
	 *
	 * @param array			$aOptions			The config options
	 *
	 * @return void
	 */
    public function setOptions($aOptions)
    {
    	return Container::getInstance()->getConfig()->setOptions($aOptions);
	}

	/**
	 * Get the value of a config option
	 *
	 * @param string		$sName				The option name
	 *
	 * @return mixed		The option value, or null if the option is unknown
	 */
	public function getOption($sName)
	{
		return Container::getInstance()->getConfig()->getOption($sName);
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
    	return Container::getInstance()->getConfig()->hasOption($sName);
	}

	/**
	 * Get the names of the options matching a given prefix
	 *
	 * @param string		$sPrefix		The prefix to match
	 *
	 * @return array		The options matching the prefix
	 */
	public function getOptionNames($sPrefix)
    {
    	return Container::getInstance()->getConfig()->getOptionNames($sPrefix);
    }

	/**
	 * Set a cache directory for the template engine
	 *
	 * @param string		$sCacheDir			The cache directory
	 *
	 * @return void
	 */
    public function setCacheDir($sCacheDir)
    {
    	Container::getInstance()->getTemplate()->setCacheDir($sCacheDir);
    }

    /**
	 * Render a template
	 *
	 * @param string		$sTemplate			The name of template to be rendered
	 * @param string		$aVars				The template vars
	 *
	 * @return string		The template content
	 */
	public function render($sTemplate, array $aVars = array())
	{
		return Container::getInstance()->getTemplate()->render($sTemplate, $aVars);
	}

	/**
	 * Get a translated string
	 *
	 * @param string		$sText				The key of the translated string
	 * @param string		$aPlaceHolders		The placeholders of the translated string
	 * @param string		$sLanguage			The language of the translated string
	 *
	 * @return string		The translated string
	 */
	public function trans($sText, array $aPlaceHolders = array(), $sLanguage = null)
	{
		return Container::getInstance()->getTranslator()->trans($sText, $aPlaceHolders, $sLanguage);
	}

	/**
	 * Minify javascript code
	 *
	 * @param string		$sJsFile			The javascript file to be minified
	 * @param string		$sMinFile			The minified javascript file
	 *
	 * @return boolean		True if the file was minified
	 */
	public function minify($sJsFile, $sMinFile)
	{
		return Container::getInstance()->getMinifier()->minify($sJsFile, $sMinFile);
	}

	/**
	 * Validate a function name
	 *
	 * @param string		$sName			The function name
	 *
	 * @return bool			True if the function name is valid, and false if not
	 */
    public function validateFunction($sName)
    {
    	return Container::getInstance()->getValidator()->validateFunction($sName);
    }

	/**
	 * Validate an event name
	 *
	 * @param string		$sName			The event name
	 *
	 * @return bool			True if the event name is valid, and false if not
	 */
    public function validateEvent($sName)
    {
    	return Container::getInstance()->getValidator()->validateEvent($sName);
    }

	/**
	 * Validate a class name
	 *
	 * @param string		$sName			The class name
	 *
	 * @return bool			True if the class name is valid, and false if not
	 */
    public function validateClass($sName)
    {
    	return Container::getInstance()->getValidator()->validateClass($sName);
    }

	/**
	 * Validate a method name
	 *
	 * @param string		$sName			The function name
	 *
	 * @return bool			True if the method name is valid, and false if not
	 */
    public function validateMethod($sName)
    {
    	return Container::getInstance()->getValidator()->validateMethod($sName);
    }
}
