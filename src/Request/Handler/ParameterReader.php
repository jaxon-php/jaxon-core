<?php

/**
 * ParameterReader.php
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

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Utils\Http\UriDetector;
use Jaxon\Utils\Http\UriException;

use function array_map;
use function call_user_func;
use function floor;
use function function_exists;
use function iconv;
use function intval;
use function is_array;
use function is_string;
use function json_decode;
use function mb_convert_encoding;
use function strcasecmp;
use function strlen;
use function substr;
use function utf8_decode;

class ParameterReader
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
     * @var UriDetector
     */
    private $xUriDetector;

    /**
     * The function which decodes input parameters.
     *
     * @var callable
     */
    private $cParamDecoder;

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
     * @param UriDetector $xUriDetector
     */
    public function __construct(Container $di, ConfigManager $xConfigManager,
        Translator $xTranslator, UriDetector $xUriDetector)
    {
        $this->di = $di;
        $this->xConfigManager = $xConfigManager;
        $this->xTranslator = $xTranslator;
        $this->xUriDetector = $xUriDetector;
    }

    /**
     * Choose the function to use to decode input data.
     *
     * @return void
     */
    private function setParamDecoder()
    {
        // Parameters are url encoded when uploading files
        $aServerParams = $this->di->getRequest()->getServerParams();
        $sContentType = '';
        if(isset($aServerParams['CONTENT_TYPE']))
        {
            $sContentType = $aServerParams['CONTENT_TYPE'];
        }
        elseif(isset($aServerParams['HTTP_CONTENT_TYPE']))
        {
            $sContentType = $aServerParams['HTTP_CONTENT_TYPE'];
        }
        $sType = 'multipart/form-data';
        if(strncmp($sContentType, $sType, strlen($sType)) !== 0)
        {
            $this->cParamDecoder = function($sParam) { return $sParam; };
            return;
        }
        $this->cParamDecoder = function($sParam) { return urldecode($sParam); };
    }

    /**
     * Choose the function to use to decode input data.
     *
     * @return void
     * @throws RequestException
     */
    private function setUtf8Decoder()
    {
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
        elseif($sEncoding === "ISO-8859-1")
        {
            $this->cUtf8Decoder = function($sStr) {
                return utf8_decode($sStr);
            };
        }
        else
        {
            throw new RequestException($this->xTranslator->trans('errors.request.conversion'));
        }
    }

    /**
     * Converts a string to a bool var
     *
     * @param string $sValue    The string to be converted
     *
     * @return bool
     */
    private function convertStringToBool(string $sValue): bool
    {
        if(strcasecmp($sValue, 'true') === 0)
        {
            return true;
        }
        if(strcasecmp($sValue, 'false') === 0)
        {
            return false;
        }
        return (intval($sValue) !== 0);
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
    private function convertValue(string $sValue)
    {
        $cType = substr($sValue, 0, 1);
        $sValue = substr($sValue, 1);
        switch($cType)
        {
        case 'S':
            return $sValue;
        case 'B':
            return $this->convertStringToBool($sValue);
        case 'N':
            return ($sValue == floor($sValue) ? (int)$sValue : (float)$sValue);
        case '*':
        default:
            return null;
        }
    }

    /**
     * Decode and convert a Jaxon request argument
     *
     * @param string $sParam    The Jaxon request argument
     *
     * @return mixed
     */
    private function decodeRequestParameter(string $sParam)
    {
        if($sParam === '')
        {
            return $sParam;
        }

        $sParam = call_user_func($this->cParamDecoder, $sParam);

        $xJson = json_decode($sParam, true);
        if($xJson !== null && $sParam != $xJson)
        {
            return $xJson;
        }
        return $this->convertValue($sParam);
    }

    /**
     * @return array
     */
    private function getRequestParameters(): array
    {
        $aParams = [];
        $xRequest = $this->di->getRequest();
        $aBody = $xRequest->getParsedBody();
        if(is_array($aBody))
        {
            if(isset($aBody['jxnargs']))
            {
                $aParams = $aBody['jxnargs'];
            }
        }
        else
        {
            $aParams = $xRequest->getQueryParams();
            if(isset($aParams['jxnargs']))
            {
                $aParams = $aParams['jxnargs'];
            }
        }
        return array_map(function($sParam) {
            return $this->decodeRequestParameter((string)$sParam);
        }, $aParams);
    }

    /**
     * Decode a Jaxon request argument from UTF8
     *
     * @param mixed $xValue    The value of the argument being decoded
     *
     * @return mixed
     */
    private function decodeUtf8Parameter($xValue)
    {
        if(is_string($xValue))
        {
            return call_user_func($this->cUtf8Decoder, $xValue);
        }
        // elseif(is_numeric($xValue) || is_bool($xValue))
        {
            return $xValue;
        }
    }

    /**
     * Decode an array of Jaxon request arguments from UTF8
     *
     * @param array $aParams
     *
     * @return array
     */
    private function decodeUtf8Parameters(array $aParams): array
    {
        $aValues = [];
        foreach($aParams as $sKey => $xValue)
        {
            // Decode the key
            $sKey = call_user_func($this->cUtf8Decoder, $sKey);
            // Decode the value
            $aValues[$sKey] = is_array($xValue) ?
                $this->decodeUtf8Parameters($xValue) : $this->decodeUtf8Parameter($xValue);
        }
        return $aValues;
    }

    /**
     * Return the array of arguments from the GET or POST data
     *
     * @return array
     * @throws RequestException
     */
    public function args(): array
    {
        $this->setParamDecoder();
        $aParams = $this->getRequestParameters();
        if(!$this->xConfigManager->getOption('core.decode_utf8'))
        {
            return $aParams;
        }
        $this->setUtf8Decoder();
        $this->xConfigManager->setOption('core.decode_utf8', false);
        return $this->decodeUtf8Parameters($aParams);
    }

    /**
     * Get the URI of the current request
     *
     * @throws UriException
     */
    public function uri(): string
    {
        return $this->xUriDetector->detect($this->di->getRequest()->getServerParams());
    }

    /**
     * Make the specified URL suitable for redirect
     *
     * @param string $sURL    The relative or fully qualified URL
     *
     * @return string
     */
    public function parseUrl(string $sURL): string
    {
        return $this->xUriDetector->redirect($sURL, $this->di->getRequest()->getServerParams());
    }
}
