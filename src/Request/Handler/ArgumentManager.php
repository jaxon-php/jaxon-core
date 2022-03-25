<?php

/**
 * ArgumentManager.php
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

use Jaxon\Di\Container;
use Jaxon\Config\ConfigManager;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Exception\RequestException;
use Psr\Http\Message\ServerRequestInterface;

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

class ArgumentManager
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * @var ConfigManager
     */
    protected $xConfigManager;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * The function which decodes utf8 string.
     *
     * @var callable
     */
    private $cUtf8Decoder;

    /**
     * The constructor
     *
     * @param Container $di
     * @param ConfigManager $xConfigManager
     * @param Translator $xTranslator
     */
    public function __construct(Container $di, ConfigManager $xConfigManager, Translator $xTranslator)
    {
        $this->di = $di;
        $this->xConfigManager = $xConfigManager;
        $this->xTranslator = $xTranslator;
    }

    /**
     * Converts a string to a bool var
     *
     * @param string $sValue    The string to be converted
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
     * Convert a Jaxon request argument to its value
     *
     * Depending on its first char, the Jaxon request argument is converted to a given type.
     *
     * @param string $sValue    The keys of the options in the file
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
            $value = !$sValue ? '' : $sValue;
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
     * Decode and convert Jaxon request arguments from JSON
     *
     * @param string $sArg    The Jaxon request argument
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
     * @param array $aDst    An array to store the decoded arguments
     * @param string $sKey    The key of the argument being decoded
     * @param string|array $mValue    The value of the argument being decoded
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
     * Return the array of arguments from the GET or POST data
     *
     * @return array
     * @throws RequestException
     */
    public function arguments(): array
    {
        $aArgs = [];
        $xRequest = $this->di->getRequest();
        if(is_array(($aBody = $xRequest->getParsedBody())) && isset($aBody['jxnargs']))
        {
            $aArgs = $aBody['jxnargs'];
        }
        elseif(is_array(($aParams = $xRequest->getQueryParams())) && isset($aParams['jxnargs']))
        {
            $aArgs = $aParams['jxnargs'];
        }

        array_walk($aArgs, [$this, '__argumentDecode']);

        if(!$this->xConfigManager->getOption('core.decode_utf8'))
        {
            return $aArgs;
        }
        // By default, no decoding
        $this->cUtf8Decoder = function($sStr) {
            return $sStr;
        };
        $sEncoding = $this->xConfigManager->getOption('core.encoding', '');
        if(function_exists('iconv'))
        {
            $this->cUtf8Decoder = function($sStr) use($sEncoding) {
                return iconv("UTF-8", $sEncoding . '//TRANSLIT', $sStr);
            };
        }
        elseif(function_exists('mb_convert_encoding'))
        {
            $this->cUtf8Decoder = function($sStr) use($sEncoding) {
                return mb_convert_encoding($sStr, $sEncoding, "UTF-8");
            };
        }
        elseif($sEncoding == "ISO-8859-1")
        {
            $this->cUtf8Decoder = function($sStr) {
                return utf8_decode($sStr);
            };
        }
        else
        {
            throw new RequestException($this->xTranslator->trans('errors.request.conversion'));
        }

        $aDst = [];
        foreach($aArgs as $sKey => &$mValue)
        {
            $this->_decode_utf8_argument($aDst, $sKey, $mValue);
        };
        $aArgs = $aDst;

        $this->xConfigManager->setOption('core.decode_utf8', false);

        return $aArgs;
    }
}
