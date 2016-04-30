<?php

namespace Xajax\Config;

class Php
{
    public static function read($sConfigFile, $sKey = '')
    {
    	$sConfigFile = realpath($sConfigFile);
    	if(!is_readable($sConfigFile))
        {
            throw new \Xajax\Exception\Config\File('access', $sConfigFile);
        }
        $aConfigOptions = include($sConfigFile);
        if(!is_array($aConfigOptions))
        {
            throw new \Xajax\Exception\Config\File('content', $sConfigFile);
        }

        // Content read from config file. Try to parse.
        Config::setOptions($aConfigOptions, $sKey);
    }
}
