<?php

namespace Xajax\Utils;

class Minifier
{
	/*
		Object: xInstance
		The only instance of the Minifier (Singleton)
	*/
	private static $xInstance = null;

	/*
		Function: getInstance
		
		Implementation of the singleton pattern: returns the one and only instance of the Minifier
		
		Returns:
		
		object : a reference to the Minifier object.
	*/
	public static function getInstance()
	{
		if(!self::$xInstance)
		{
			self::$xInstance = new Minifier();    
		}
		return self::$xInstance;
	}

	private function __construct()
    {}

	public function minify($sCode)
    {
    	return \WebSharks\JsMinifier\Core::compress($sCode);
    }
}
