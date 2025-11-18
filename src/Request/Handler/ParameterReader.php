<?php

/**
 * ParameterReader.php
 *
 * This class reads the input arguments from the GET or POST data of the request.
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
use Psr\Http\Message\ServerRequestInterface;

use function function_exists;
use function iconv;
use function is_array;
use function is_string;
use function json_decode;
use function mb_convert_encoding;
use function strlen;
use function strncmp;
use function urldecode;

class ParameterReader
{
    /**
     * The constructor
     *
     * @param Container $di
     * @param Translator $xTranslator
     * @param ConfigManager $xConfigManager
     * @param UriDetector $xUriDetector
     */
    public function __construct(private Container $di, private Translator $xTranslator,
        private ConfigManager $xConfigManager, private UriDetector $xUriDetector)
    {}

    /**
     * Decode input data.
     *
     * @param string $sStr
     *
     * @return string
     */
    private function decodeStr(string $sStr): string
    {
        $aServerParams = $this->di->getServerParams();
        $sContentType = $aServerParams['CONTENT_TYPE'] ?? $aServerParams['HTTP_CONTENT_TYPE'] ?? '';
        $sType = 'multipart/form-data';
        // Parameters are url encoded when uploading files
        return strncmp($sContentType, $sType, strlen($sType)) !== 0 ?
            $sStr : urldecode($sStr);
    }

    /**
     * Decode input data.
     *
     * @param string $sStr
     *
     * @return string
     * @throws RequestException
     */
    private function decoderUtf8Str(string $sStr): string
    {
        $sEncoding = $this->xConfigManager->getOption('core.encoding', '');
        if(function_exists('iconv'))
        {
            return iconv("UTF-8", $sEncoding . '//TRANSLIT', $sStr);
        }
        if(function_exists('mb_convert_encoding'))
        {
            return mb_convert_encoding($sStr, $sEncoding, "UTF-8");
        }
        // By default, no decoding
        // return $sStr;
        throw new RequestException($this->xTranslator->trans('errors.request.conversion'));
    }

    /**
     * Choose the function to use to decode input data.
     *
     * @param string $sParam
     *
     * @return string
     */
    private function decodeRequestParameter(string $sParam): string
    {
        $sParam = $this->decodeStr($sParam);
        if(!$this->xConfigManager->getOption('core.decode_utf8'))
        {
            return $sParam;
        }
        $this->xConfigManager->setOption('core.decode_utf8', false);
        return $this->decoderUtf8Str($sParam);
    }

    /**
     * @param ServerRequestInterface $xRequest
     *
     * @return ServerRequestInterface
     */
    public function setRequestParameter(ServerRequestInterface $xRequest): ServerRequestInterface
    {
        $aBody = $xRequest->getParsedBody();
        $aParams = is_array($aBody) ? $aBody : $xRequest->getQueryParams();
        // Check if Jaxon call parameters are present.
        if(isset($aParams['jxncall']) && is_string($aParams['jxncall']))
        {
            $xRequest = $xRequest->withAttribute('jxncall', json_decode($this
                ->decodeRequestParameter($aParams['jxncall']), true));
        }
        // Check if Jaxon bags parameters are present.
        if(isset($aParams['jxnbags']) && is_string($aParams['jxnbags']))
        {
            $xRequest = $xRequest->withAttribute('jxnbags', json_decode($this
                ->decodeRequestParameter($aParams['jxnbags']), true));
        }
        return $xRequest;
    }

    /**
     * Get the URI of the current request
     *
     * @throws UriException
     */
    public function uri(): string
    {
        return $this->xUriDetector->detect($this->di->getServerParams());
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
        return $this->xUriDetector->redirect($sURL, $this->di->getServerParams());
    }
}
