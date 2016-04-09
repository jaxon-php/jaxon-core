<?php

namespace Xajax\Utils;

class Minifier
{
	/**
	 * Minify javascript code
	 *
	 * @param string		$sCode				The javascript code to be minified
	 *
	 * @return string		The minified code
	 */
    public function minify($sCode)
    {
    	return \WebSharks\JsMinifier\Core::compress($sCode);
    }
}
