<?php

namespace Xajax\Config;

class Yaml
{
    public static function read($sConfigFile, $sKey = '')
    {
    	$sConfigFile = realpath($sConfigFile);
    	if(!extension_loaded('yaml'))
        {
            throw new \Xajax\Exception\Config\Yaml('install');
        }
        if(!is_readable($sConfigFile))
        {
            throw new \Xajax\Exception\Config\File('access', $sConfigFile);
        }
        $aConfigOptions = yaml_parse_file($sConfigFile);
        if(!is_array($aConfigOptions))
        {
            throw new \Xajax\Exception\Config\File('content', $sConfigFile);
        }

        // Content read from config file. Try to parse.
        Config::setOptions($aConfigOptions, $sKey);
    }
}
