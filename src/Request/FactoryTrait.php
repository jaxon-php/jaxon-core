<?php

/**
 * FactoryTrait.php - Trait for Xajax Request Factory
 *
 * Make functions of the Xajax Request Factory class available to Xajax classes.
 *
 * @package xajax-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax\Request;

use Xajax\Utils\Container;

trait FactoryTrait
{
	/**
	 * The \Xajax\Request\Support\CallableObject instance associated to the Xajax object using this trait
	 *
	 * @var \Xajax\Request\Support\CallableObject
	 */
	private $xXajaxCallable = null;

	/**
	 * Set the associated \Xajax\Request\Support\CallableObject instance
	 *
	 * @param object		$xXajaxCallable			The \Xajax\Request\Support\CallableO object instance
	 *
	 * @return void
	 */
	public function setXajaxCallable($xXajaxCallable)
	{
		$this->xXajaxCallable = $xXajaxCallable;
	}

	/**
	 * Get the Xajax class name
	 * 
	 * This is the name to be used in Xajax javascript calls.
	 *
	 * @return string		The Xajax class name
	 */
	public function getXajaxClassName()
	{
		if(!$this->xXajaxCallable)
		{
			// Make the Xajax class name for a class without an associated callable
			// !! Warning !! This can happen only if this object is not registered with the Xajax libary
			$xReflectionClass = new \ReflectionClass(get_class($this));
			// Return the class name without the namespace.
			return $xReflectionClass->getShortName();
		}
		return $this->xXajaxCallable->getName();
	}

	/**
	 * Get the Xajax class namespace
	 *
	 * @return string		The Xajax class namespace
	 */
	public function getXajaxNamespace()
	{
		if(!$this->xXajaxCallable)
		{
			// Return an empty string.
			return '';
		}
		return $this->xXajaxCallable->getNamespace();
	}

	/**
	 * Get the Xajax class path
	 *
	 * @return string		The Xajax class path
	 */
	public function getXajaxClassPath()
	{
		if(!$this->xXajaxCallable)
		{
			// Return an empty string.
			return '';
		}
		return $this->xXajaxCallable->getPath();
	}

	/**
	 * Return the javascript call to an Xajax object method
	 *
	 * @param string 		$sName			The method (without class) name
	 * @param ...			$xParams		The parameters of the method
	 *
	 * @return object
	 */
	public function request($sMethodName)
	{
		$sMethodName = (string)$sMethodName;
		$aArgs = func_get_args();
		// Prepend the class name to the method name
		$aArgs[0] = $this->getXajaxClassName() . '.' . $sMethodName;
		// Make the request
		return call_user_func_array('\Xajax\Request\Factory::make', $aArgs);
	}

	/**
	 * Make the pagination links for a registered Xajax class method
	 *
	 * @param integer $itemsTotal the total number of items
	 * @param integer $itemsPerPage the number of items per page page
	 * @param integer $currentPage the current page
	 * @param string $method the name of the method
	 * @param ... $parameters the parameters of the method
	 *
	 * @return string the pagination links
	 */
	public function paginate($itemsTotal, $itemsPerPage, $currentPage, $method)
	{
		// Get the args list starting from the $method
		$aArgs = array_slice(func_get_args(), 3);
		// Make the request
		$request = call_user_func_array(array($this, 'request'), $aArgs);
		// Append the page number to the parameter list, if not yet given.
		if(!$request->hasPageNumber())
		{
			$request->addParameter(XAJAX_PAGE_NUMBER, 0);
		}
		$paginator = Container::getInstance()->getPaginator();
		$paginator->setup($itemsTotal, $itemsPerPage, $currentPage, $request);
		return $paginator->toHtml();
	}
}
