<?php

/**
 * URI.php - Jaxon request URI detector
 *
 * Detect and parse the URI of the Jaxon request being processed.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils;

class URI
{
    /**
     * Detect the URI of the current request
     *
     * @return string        The URI
     */
    public static function detect()
    {
        $aURL = array();
        // Try to get the request URL
        if(!empty($_SERVER['REQUEST_URI']))
        {
            $_SERVER['REQUEST_URI'] = str_replace(array('"',"'",'<','>'), array('%22','%27','%3C','%3E'), $_SERVER['REQUEST_URI']);
            $aURL = parse_url($_SERVER['REQUEST_URI']);
        }
        
        // Fill in the empty values
        if(empty($aURL['scheme']))
        {
            if(!empty($_SERVER['HTTP_SCHEME']))
            {
                $aURL['scheme'] = $_SERVER['HTTP_SCHEME'];
            }
            else
            {
                $aURL['scheme'] = ((!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? 'https' : 'http');
            }
        }
        
        if(empty($aURL['host']))
        {
            if(!empty($_SERVER['HTTP_X_FORWARDED_HOST']))
            {
                if(strpos($_SERVER['HTTP_X_FORWARDED_HOST'], ':') > 0)
                {
                    list($aURL['host'], $aURL['port']) = explode(':', $_SERVER['HTTP_X_FORWARDED_HOST']);
                }
                else
                {
                    $aURL['host'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
                }
            }
            elseif(!empty($_SERVER['HTTP_HOST']))
            {
                if(strpos($_SERVER['HTTP_HOST'], ':') > 0)
                {
                    list($aURL['host'], $aURL['port']) = explode(':', $_SERVER['HTTP_HOST']);
                }
                else
                {
                    $aURL['host'] = $_SERVER['HTTP_HOST'];
                }
            }
            elseif(!empty($_SERVER['SERVER_NAME']))
            {
                $aURL['host'] = $_SERVER['SERVER_NAME'];
            }
            else
            {
                throw new \Jaxon\Exception\URI();
            }
        }
        
        if(empty($aURL['port']) && !empty($_SERVER['SERVER_PORT']))
        {
            $aURL['port'] = $_SERVER['SERVER_PORT'];
        }
        
        if(!empty($aURL['path']) && strlen(basename($aURL['path'])) == 0)
        {
            unset($aURL['path']);
        }
        
        if(empty($aURL['path']))
        {
            $sPath = array();
            if(!empty($_SERVER['PATH_INFO']))
            {
                $sPath = parse_url($_SERVER['PATH_INFO']);
            }
            else
            {
                $sPath = parse_url($_SERVER['PHP_SELF']);
            }
            if(isset($sPath['path']))
            {
                $aURL['path'] = str_replace(array('"',"'",'<','>'), array('%22','%27','%3C','%3E'), $sPath['path']);
            }
            unset($sPath);
        }
        
        if(empty($aURL['query']))
        {
            $aURL['query'] = empty($_SERVER['QUERY_STRING']) ? '' : $_SERVER['QUERY_STRING'];
        }
        
        if(!empty($aURL['query']))
        {
            $aURL['query'] = '?'.$aURL['query'];
        }
        
        // Build the URL: Start with scheme, user and pass
        $sURL = $aURL['scheme'].'://';
        if(!empty($aURL['user']))
        {
            $sURL.= $aURL['user'];
            if(!empty($aURL['pass']))
            {
                $sURL.= ':'.$aURL['pass'];
            }
            $sURL.= '@';
        }
        
        // Add the host
        $sURL.= $aURL['host'];
        
        // Add the port if needed
        if(!empty($aURL['port'])
                && (($aURL['scheme'] == 'http' && $aURL['port'] != 80)
                        || ($aURL['scheme'] == 'https' && $aURL['port'] != 443)))
        {
            $sURL.= ':'.$aURL['port'];
        }
        
        // Add the path and the query string
        $sURL.= $aURL['path'].$aURL['query'];
        
        // Clean up
        unset($aURL);
        
        $aURL = explode("?", $sURL);
        
        if(1 < count($aURL))
        {
            $aQueries = explode("&", $aURL[1]);
        
            foreach($aQueries as $sKey => $sQuery)
            {
                if("jxnGenerate" == substr($sQuery, 0, 11))
                    unset($aQueries[$sKey]);
            }
                
            $sQueries = implode("&", $aQueries);
                
            $aURL[1] = $sQueries;
                
            $sURL = implode("?", $aURL);
        }
        
        return $sURL;
    }
}
