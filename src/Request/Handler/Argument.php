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

use Jaxon\Exception\SetupException;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Translation\Translator;

use function strcasecmp;
use function is_numeric;
use function is_string;
use function is_array;
use function is_bool;
use function substr;
use function strlen;
use function floor;
use function json_decode;
use function call_user_func;
use function array_walk;
use function function_exists;
use function iconv;
use function mb_convert_encoding;
use function utf8_decode;

class Argument
{
    /*
     * Request methods
     */
    const METHOD_UNKNOWN = 0;
    const METHOD_GET = 1;
    const METHOD_POST = 2;

    /**
     * @var Config
     */
    protected $xConfig;

    /**
     * @var Translator
     */
    protected $xTranslator;

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
     *
     * @param Config $xConfig
     * @param Translator $xTranslator
     */
    public function __construct(Config $xConfig, Translator $xTranslator)
    {
        $this->xConfig = $xConfig;
        $this->xTranslator = $xTranslator;
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
     * @return int
     */
    public function getRequestMethod(): int
    {
        return $this->nMethod;
    }

    /**
     * Return true if the current request method is GET
     *
     * @return bool
     */
    public function requestMethodIsGet(): bool
    {
        return ($this->getRequestMethod() === self::METHOD_GET);
    }

    /**
     * Converts a string to a bool var
     *
     * @param string        $sValue                The string to be converted
     *
     * @return bool
     */
    private function __convertStringToBool(string $sValue): bool
    {
        if(strcasecmp($sValue, 'true') === 0)
        {
            return true;
        }
        if(strcasecmp($sValue, 'false') === 0)
        {
            return false;
        }
        if(is_numeric($sValue))
        {
            return ($sValue !== '0');
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
     * @return string|bool|integer|double|null
     */
    private function __convertValue(string $sValue)
    {
        $cType = substr($sValue, 0, 1);
        $sValue = substr($sValue, 1);
        switch($cType)
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
     * @return void
     */
    private function __argumentDecode(string &$sArg)
    {
        if($sArg === '')
        {
            return;
        }

        // Arguments are url encoded when uploading files
        $sType = 'multipart/form-data';
        $nLen = strlen($sType);
        $sContentType = '';
        if(isset($_SERVER['CONTENT_TYPE']))
        {
            $sContentType = substr($_SERVER['CONTENT_TYPE'], 0, $nLen);
        }
        elseif(isset($_SERVER['HTTP_CONTENT_TYPE']))
        {
            $sContentType = substr($_SERVER['HTTP_CONTENT_TYPE'], 0, $nLen);
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
     * Decode an Jaxon request argument from UTF8
     *
     * @param array             $aDst           An array to store the decoded arguments
     * @param string            $sKey           The key of the argument being decoded
     * @param string|array      $mValue         The value of the argument being decoded
     *
     * @return void
     */
    private function _decode_utf8_argument(array &$aDst, string $sKey, $mValue)
    {
        // Decode the key
        $sDestKey = call_user_func($this->cUtf8Decoder, $sKey);

        if(is_array($mValue))
        {
            $aDst[$sDestKey] = [];
            foreach($mValue as $_sKey => &$_mValue)
            {
                $this->_decode_utf8_argument($aDst[$sDestKey], $_sKey, $_mValue);
            }
        }
        elseif(is_numeric($mValue) || is_bool($mValue))
        {
            $aDst[$sDestKey] = $mValue;
        }
        elseif(is_string($mValue))
        {
            $aDst[$sDestKey] = call_user_func($this->cUtf8Decoder, $mValue);
        }
    }

    /**
     * Return the array of arguments that were extracted and parsed from the GET or POST data
     *
     * @return array
     * @throws SetupException
     */
    public function process(): array
    {
        // if(get_magic_quotes_gpc() == 1)
        // {
        //     \array_walk($this->aArgs, [$this, '__argumentStripSlashes']);
        // }
        array_walk($this->aArgs, [$this, '__argumentDecode']);

        if(!$this->xConfig->getOption('core.decode_utf8'))
        {
            return $this->aArgs;
        }
        // By default, no decoding
        $this->cUtf8Decoder = function($sStr) {
            return $sStr;
        };
        if(function_exists('iconv'))
        {
            $this->cUtf8Decoder = function($sStr) {
                return iconv("UTF-8", $this->xConfig->getOption('core.encoding') . '//TRANSLIT', $sStr);
            };
        }
        elseif(function_exists('mb_convert_encoding'))
        {
            $this->cUtf8Decoder = function($sStr) {
                return mb_convert_encoding($sStr, $this->xConfig->getOption('core.encoding'), "UTF-8");
            };
        }
        elseif($this->xConfig->getOption('core.encoding') == "ISO-8859-1")
        {
            $this->cUtf8Decoder = function($sStr) {
                return utf8_decode($sStr);
            };
        }
        else
        {
            throw new SetupException($this->xTranslator->trans('errors.request.conversion'));
        }

        $aDst = [];
        foreach($this->aArgs as $sKey => &$mValue)
        {
            $this->_decode_utf8_argument($aDst, $sKey, $mValue);
        };
        $this->aArgs = $aDst;

        $this->setOption('core.decode_utf8', false);

        return $this->aArgs;
    }
}
