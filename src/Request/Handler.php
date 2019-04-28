<?php

/**
 * Manager.php - Jaxon Request Manager
 *
 * This class processes the input arguments from the GET or POST data of the request.
 * If this is a request for the initial page load, no arguments will be processed.
 * During a jaxon request, any arguments found in the GET or POST will be converted to a PHP array.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request;

use Jaxon\Jaxon;

class Handler
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * The plugin manager.
     *
     * @var Jaxon\Plugin\Manager
     */
    private $xPluginManager;

    /**
     * An array of arguments received via the GET or POST parameter jxnargs.
     *
     * @var array
     */
    private $aArgs;

    /**
     * Stores the method that was used to send the arguments from the client.
     * Will be one of: Jaxon::METHOD_UNKNOWN, Jaxon::METHOD_GET, Jaxon::METHOD_POST.
     *
     * @var integer
     */
    private $nMethod;

    /**
     * The constructor
     *
     * Get and decode the arguments of the HTTP request
     */
    public function __construct(\Jaxon\Plugin\Manager $xPluginManager)
    {
        $this->xPluginManager = $xPluginManager;

        $this->aArgs = [];
        $this->nMethod = Jaxon::METHOD_UNKNOWN;

        if(isset($_POST['jxnargs']))
        {
            $this->nMethod = Jaxon::METHOD_POST;
            $this->aArgs = $_POST['jxnargs'];
        }
        elseif(isset($_GET['jxnargs']))
        {
            $this->nMethod = Jaxon::METHOD_GET;
            $this->aArgs = $_GET['jxnargs'];
        }
        if(get_magic_quotes_gpc() == 1)
        {
            array_walk($this->aArgs, array(&$this, '__argumentStripSlashes'));
        }
        array_walk($this->aArgs, array(&$this, '__argumentDecode'));
    }

    /**
     * Converts a string to a boolean var
     *
     * @param string        $sValue                The string to be converted
     *
     * @return boolean
     */
    private function __convertStringToBool($sValue)
    {
        if(strcasecmp($sValue, 'true') == 0)
        {
            return true;
        }
        if(strcasecmp($sValue, 'false') == 0)
        {
            return false;
        }
        if(is_numeric($sValue))
        {
            if($sValue == 0)
            {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Strip the slashes from a string
     *
     * @param string        $sArg                The string to be stripped
     *
     * @return string
     */
    private function __argumentStripSlashes(&$sArg)
    {
        if(!is_string($sArg))
        {
            return '';
        }
        $sArg = stripslashes($sArg);
    }

    /**
     * Convert an Jaxon request argument to its value
     *
     * Depending of its first char, the Jaxon request argument is converted to a given type.
     *
     * @param string        $sValue                The keys of the options in the file
     *
     * @return mixed
     */
    private function __convertValue($sValue)
    {
        $cType = substr($sValue, 0, 1);
        $sValue = substr($sValue, 1);
        switch ($cType)
        {
            case 'S':
                $value = ($sValue === false ? '' : $sValue);
                break;
            case 'B':
                $value = $this->__convertStringToBool($sValue);
                break;
            case 'N':
                $value = ($sValue == floor($sValue) ? (int)$sValue : (float)$sValue);
                break;
            case '*':
            default:
                $value = null;
                break;
        }
        return $value;
    }

    /**
     * Decode and convert an Jaxon request argument from JSON
     *
     * @param string        $sArg                The Jaxon request argument
     *
     * @return mixed
     */
    private function __argumentDecode(&$sArg)
    {
        if($sArg == '')
        {
            return '';
        }

        // Arguments are url encoded when uploading files
        $sType = 'multipart/form-data';
        $iLen = strlen($sType);
        $sContentType = '';
        if(key_exists('CONTENT_TYPE', $_SERVER))
        {
            $sContentType = substr($_SERVER['CONTENT_TYPE'], 0, $iLen);
        }
        elseif(key_exists('HTTP_CONTENT_TYPE', $_SERVER))
        {
            $sContentType = substr($_SERVER['HTTP_CONTENT_TYPE'], 0, $iLen);
        }
        if($sContentType == $sType)
        {
            $sArg = urldecode($sArg);
        }

        $data = json_decode($sArg, true);

        if($data !== null && $sArg != $data)
        {
            $sArg = $data;
        }
        else
        {
            $sArg = $this->__convertValue($sArg);
        }
    }

    /**
     * Decode an Jaxon request argument and convert to UTF8 with iconv
     *
     * @param string|array        $mArg                The Jaxon request argument
     *
     * @return void
     */
    private function __argumentDecodeUTF8_iconv(&$mArg)
    {
        if(is_array($mArg))
        {
            foreach($mArg as $sKey => &$xArg)
            {
                $sNewKey = $sKey;
                $this->__argumentDecodeUTF8_iconv($sNewKey);
                if($sNewKey != $sKey)
                {
                    $mArg[$sNewKey] = $xArg;
                    unset($mArg[$sKey]);
                    $sKey = $sNewKey;
                }
                $this->__argumentDecodeUTF8_iconv($xArg);
            }
        }
        elseif(is_string($mArg))
        {
            $mArg = iconv("UTF-8", $this->getOption('core.encoding') . '//TRANSLIT', $mArg);
        }
    }

    /**
     * Decode an Jaxon request argument and convert to UTF8 with mb_convert_encoding
     *
     * @param string|array        $mArg                The Jaxon request argument
     *
     * @return void
     */
    private function __argumentDecodeUTF8_mb_convert_encoding(&$mArg)
    {
        if(is_array($mArg))
        {
            foreach($mArg as $sKey => &$xArg)
            {
                $sNewKey = $sKey;
                $this->__argumentDecodeUTF8_mb_convert_encoding($sNewKey);
                if($sNewKey != $sKey)
                {
                    $mArg[$sNewKey] = $xArg;
                    unset($mArg[$sKey]);
                    $sKey = $sNewKey;
                }
                $this->__argumentDecodeUTF8_mb_convert_encoding($xArg);
            }
        }
        elseif(is_string($mArg))
        {
            $mArg = mb_convert_encoding($mArg, $this->getOption('core.encoding'), "UTF-8");
        }
    }

    /**
     * Decode an Jaxon request argument from UTF8
     *
     * @param string|array        $mArg                The Jaxon request argument
     *
     * @return void
     */
    private function __argumentDecodeUTF8_utf8_decode(&$mArg)
    {
        if(is_array($mArg))
        {
            foreach($mArg as $sKey => &$xArg)
            {
                $sNewKey = $sKey;
                $this->__argumentDecodeUTF8_utf8_decode($sNewKey);

                if($sNewKey != $sKey)
                {
                    $mArg[$sNewKey] = $xArg;
                    unset($mArg[$sKey]);
                    $sKey = $sNewKey;
                }

                $this->__argumentDecodeUTF8_utf8_decode($xArg);
            }
        }
        elseif(is_string($mArg))
        {
            $mArg = utf8_decode($mArg);
        }
    }

    /**
     * Return the method that was used to send the arguments from the client
     *
     * The method is one of: Jaxon::METHOD_UNKNOWN, Jaxon::METHOD_GET, Jaxon::METHOD_POST.
     *
     * @return integer
     */
    public function getRequestMethod()
    {
        return $this->nMethod;
    }

    /**
     * Return the array of arguments that were extracted and parsed from the GET or POST data
     *
     * @return array
     */
    public function processArguments()
    {
        if(($this->getOption('core.decode_utf8')))
        {
            $sFunction = '';

            if(function_exists('iconv'))
            {
                $sFunction = "iconv";
            }
            elseif(function_exists('mb_convert_encoding'))
            {
                $sFunction = "mb_convert_encoding";
            }
            elseif($this->getOption('core.encoding') == "ISO-8859-1")
            {
                $sFunction = "utf8_decode";
            }
            else
            {
                throw new \Jaxon\Exception\Error($this->trans('errors.request.conversion'));
            }

            $mFunction = array(&$this, '__argumentDecodeUTF8_' . $sFunction);
            array_walk($this->aArgs, $mFunction);
            $this->setOption('core.decode_utf8', false);
        }

        return $this->aArgs;
    }

    /**
     * Check if the current request can be processed
     *
     * Calls each of the request plugins and determines if the current request can be processed by one of them.
     * If no processor identifies the current request, then the request must be for the initial page load.
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        foreach($this->xPluginManager->getRequestPlugins() as $xPlugin)
        {
            if($xPlugin->getName() != Jaxon::FILE_UPLOAD && $xPlugin->canProcessRequest())
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Process the current request
     *
     * Calls each of the request plugins to request that they process the current request.
     * If any plugin processes the request, it will return true.
     *
     * @return boolean
     */
    public function processRequest()
    {
        foreach($this->xPluginManager->getRequestPlugins() as $xPlugin)
        {
            if($xPlugin->getName() != Jaxon::FILE_UPLOAD && $xPlugin->canProcessRequest())
            {
                $xUploadPlugin = $this->xPluginManager->getRequestPlugin(Jaxon::FILE_UPLOAD);
                // Process uploaded files
                if($xUploadPlugin != null)
                {
                    $xUploadPlugin->processRequest();
                }
                // Process the request
                return $xPlugin->processRequest();
            }
        }
        // Todo: throw an exception
        return false;
    }
}
