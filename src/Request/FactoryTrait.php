<?php

trait FactoryTrait
{
	private $xXajaxCallable = null;

	/**
	 * Set the associated Xajax callable object
	 *
	 * @param object		$xXajaxCallable			The associated Xajax callable object
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
		if(($this->xXajaxCallable))
		{
			return $this->xXajaxCallable->getName();
		}
		// Make the Xajax class name for a class without an associated callable
		// !! Warning !! This can happen only if this object is not registered with the Xajax libary
		$xReflectionClass = new \ReflectionClass(get_class($this));
		// Return the class name without the namespace.
		return $xReflectionClass->getShortName();
	}

	/**
	 * Return the javascript call to an Xajax object method
	 *
	 * @param string 		$sName			The method (without class) name
	 * @param ...			$xParams		The parameters of the method
	 *
	 * @return object
	 */
	public function request()
	{
		// There should be at least on argument to this method, the name of the class method
		if(($nArgs = func_num_args()) < 1 || !is_string(($sName = func_get_arg(0))))
		{
			return null;
		}
		$aArgs = func_get_args();
		// Prepend the class name to the method name
		$aArgs[0] = $this->getXajaxClassName() . '.' . $aArgs[0];
		// Make the request
		return call_user_func_array('\Xajax\Request\Factory::make', $aArgs);
	}
}
