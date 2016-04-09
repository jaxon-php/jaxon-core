<?php

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
	 * @param string		$sCode				The javascript code to be minified
	 *
	 * @return string		The minified code
	 */
	public function minify($sCode)
	{
		return Container::getInstance()->getMinifier()->minify($sCode);
	}
}
