<?php

/**
 * SendResponseTrait.php
 *
 * Send Jaxon ajax response.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Thierry Feuzeu
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax\Traits;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Response\Manager\ResponseManager;

use function gmdate;
use function header;
use function headers_sent;
use function http_response_code;
use function intval;

trait SendResponseTrait
{
    /**
     * @return Container
     */
    abstract public function di(): Container;

    /**
     * @return ConfigManager
     */
    abstract public function config(): ConfigManager;

    /**
     * @return Translator
     */
    abstract public function translator(): Translator;

    /**
     * @return ResponseManager
     */
    abstract public function getResponseManager(): ResponseManager;

    /**
     * Prints the response to the output stream, thus sending the response to the browser
     *
     * @return mixed
     * @throws RequestException
     */
    public function httpResponse(string $sCode = '200'): mixed
    {
        if(!$this->config()->getOption('core.response.send', false))
        {
            return null;
        }

        // Check to see if headers have already been sent out, in which case we can't do our job
        if(headers_sent($sFilename, $nLineNumber))
        {
            throw new RequestException($this->translator()
                ->trans('errors.output.already-sent', [
                    'location' => "$sFilename:$nLineNumber",
                ]) . "\n" . $this->translator()->trans('errors.output.advice'));
        }
        if(empty($sContent = $this->getResponseManager()->getOutput()))
        {
            return null;
        }

        // Set the HTTP response code
        http_response_code(intval($sCode));

        if($this->di()->getRequest()->getMethod() === 'GET')
        {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
        }
        header('Content-Type: ' . $this->getResponseManager()->getContentType());

        print $sContent;
        if($this->config()->getOption('core.process.exit', false))
        {
            exit();
        }
        return null;
    }
}
