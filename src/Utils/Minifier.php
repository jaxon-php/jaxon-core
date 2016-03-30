<?php

namespace Xajax\Utils;

class Minifier
{
    public function minify($sCode)
    {
    	return \WebSharks\JsMinifier\Core::compress($sCode);
    }
}
