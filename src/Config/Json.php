<?php

namespace Xajax\Config;

class Json
{
    public static function read($sConfigFile, $sKey = '')
    {
    	$sConfigFile = realpath($sConfigFile);
        if(!is_readable($sConfigFile))
        {
        	throw new \Xajax\Exception\Config\File('access', $sConfigFile);
        }
        $sFileContent = file_get_contents($sConfigFile);
        $aConfigOptions = json_decode($sFileContent, true);
        if(!is_array($aConfigOptions))
        {
        	throw new \Xajax\Exception\Config\File('content', $sConfigFile);
        }

        // Content read from config file. Try to parse.
        Config::setOptions($aConfigOptions, $sKey);
    }
}
