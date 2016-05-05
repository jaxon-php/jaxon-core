<?php

namespace Xajax\Utils;

use MatthiasMullie\Minify\JS as JsMinifier;

class Minifier
{
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
    	$xJsMinifier = new JsMinifier();
    	$xJsMinifier->add($sJsFile);
    	$xJsMinifier->minify($sMinFile);
    	return is_file($sMinFile);
    }
}
