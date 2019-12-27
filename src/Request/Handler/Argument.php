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

namespace Jaxon\Request\Handler;

use Jaxon\Exception\Error;

class Argument
{
    use \Jaxon\Features\Config;
    use \Jaxon\Features\Translator;

    /*
     * Request methods
     */
    const METHOD_UNKNOWN = 0;
    const METHOD_GET = 1;
    const METHOD_POST = 2;

    /**
     * An array of arguments received via the GET or POST parameter jxnargs.
     *
     * @var array
     */
    private $aArgs;

    /**
     * Stores the method that was used to send the arguments from the client.
     * Will be one of: self::METHOD_UNKNOWN, self::METHOD_GET, self::METHOD_POST.
     *
     * @var integer
     */
    private $nMethod;

    /**
     * The function which decodes utf8 string.
     *
     * @var callable
     */
    private $cUtf8Decoder;

    /**
     * The constructor
     *
     * Get and decode the arguments of the HTTP request
     */
    public function __construct()
    {
        $this->aArgs = [];
        $this->nMethod = self::METHOD_UNKNOWN;

        if(isset($_POST['jxnargs']))
        {
            $this->nMethod = self::METHOD_POST;
            $this->aArgs = $_POST['jxnargs'];
        }
        elseif(isset($_GET['jxnargs']))
        {
            $this->nMethod = self::METHOD_GET;
            $this->aArgs = $_GET['jxnargs'];
        }
    }

    /**
     * Return the method that was used to send the arguments from the client
     *
     * The method is one of: self::METHOD_UNKNOWN, self::METHOD_GET, self::METHOD_POST.
     *
     * @return integer
     */
    public function getRequestMethod()
    {
        return $this->nMethod;
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
        if(\strcasecmp($sValue, 'true') == 0)
        {
            return true;
        }
        if(\strcasecmp($sValue, 'false') == 0)
        {
            return false;
        }
        if(\is_numeric($sValue))
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
    // private function __argumentStripSlashes(&$sArg)
    // {
    //     if(!is_string($sArg))
    //     {
    //         return '';
    //     }
    //     $sArg = stripslashes($sArg);
    //     return $sArg;
    // }

    /**
     * Convert an Jaxon request argument to its value
     *
     * Depending of its first char, the Jaxon request argument is converted to a given type.
     *
     * @param string        $sValue                The keys of the options in the file
     *
     * @return string|boolean|integer|double|null
     */
    private function __convertValue($sValue)
    {
        $cType = \substr($sValue, 0, 1);
        $sValue = \substr($sValue, 1);
        switch($cType)
        {
        case 'S':
            $value = ($sValue === false ? '' : $sValue);
            break;
        case 'B':
            $value = $this->__convertStringToBool($sValue);
            break;
        case 'N':
            $value = ($sValue == \floor($sValue) ? (int)$sValue : (float)$sValue);
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
     * @return string|null
     */
    private function __argumentDecode(&$sArg)
    {
        if($sArg == '')
        {
            return '';
        }

        // Arguments are url encoded when uploading files
        $sType = 'multipart/form-data';
        $iLen = \strlen($sType);
        $sContentType = '';
        if(\key_exists('CONTENT_TYPE', $_SERVER))
        {
            $sContentType = \substr($_SERVER['CONTENT_TYPE'], 0, $iLen);
        }
        elseif(\key_exists('HTTP_CONTENT_TYPE', $_SERVER))
        {
            $sContentType = \substr($_SERVER['HTTP_CONTENT_TYPE'], 0, $iLen);
        }
        if($sContentType == $sType)
        {
            $sArg = \urldecode($sArg);
        }

        $data = \json_decode($sArg, true);

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
     * Decode an Jaxon request argument from UTF8
     *
     * @param array             $aDst           An array to store the decoded arguments
     * @param string            $sKey           The key of the argument being decoded
     * @param string|array      $mValue         The value of the argument being decoded
     *
     * @return void
     */
    private function _decode_utf8_argument(array &$aDst, $sKey, $mValue)
    {
        $sDestKey = $sKey;
        // Decode the key
        if(\is_string($sDestKey))
        {
            $sDestKey = \call_user_func($this->cUtf8Decoder, $sDestKey);
        }

        if(is_array($mValue))
        {
            $aDst[$sDestKey] = [];
            foreach($mValue as $_sKey => &$_mValue)
            {
                $this->_decode_utf8_argument($aDst[$sDestKey], $_sKey, $_mValue);
            }
        }
        elseif(\is_numeric($mValue) || \is_bool($mValue))
        {
            $aDst[$sDestKey] = $mValue;
        }
        elseif(\is_string($mValue))
        {
            $aDst[$sDestKey] = \call_user_func($this->cUtf8Decoder, $mValue);
        }
    }

    /**
     * Return the array of arguments that were extracted and parsed from the GET or POST data
     *
     * @return array
     */
    public function process()
    {
        // if(get_magic_quotes_gpc() == 1)
        // {
        //     \array_walk($this->aArgs, [$this, '__argumentStripSlashes']);
        // }
        \array_walk($this->aArgs, [$this, '__argumentDecode']);

        if(($this->getOption('core.decode_utf8')))
        {
            // By default, no decoding
            $this->cUtf8Decoder = function($sStr) {
                return $sStr;
            };
            if(\function_exists('iconv'))
            {
                $this->cUtf8Decoder = function($sStr) {
                    return \iconv("UTF-8", $this->getOption('core.encoding') . '//TRANSLIT', $sStr);
                };
            }
            elseif(\function_exists('mb_convert_encoding'))
            {
                $this->cUtf8Decoder = function($sStr) {
                    return \mb_convert_encoding($sStr, $this->getOption('core.encoding'), "UTF-8");
                };
            }
            elseif($this->getOption('core.encoding') == "ISO-8859-1")
            {
                $this->cUtf8Decoder = function($sStr) {
                    return \utf8_decode($sStr);
                };
            }
            else
            {
                throw new Error($this->trans('errors.request.conversion'));
            }

            $aDst = [];
            foreach($this->aArgs as $sKey => &$mValue)
            {
                $this->_decode_utf8_argument($aDst, $sKey, $mValue);
            };
            $this->aArgs = $aDst;

            $this->setOption('core.decode_utf8', false);
        }

        return $this->aArgs;
    }
}
