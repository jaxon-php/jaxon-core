<?php

/**
 * SendTrait.php
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

namespace Jaxon\App\Ajax;

use Jaxon\Exception\RequestException;

use function gmdate;
use function header;
use function headers_sent;
use function http_response_code;
use function intval;

trait SendTrait
{
    /**
     * Prints the response to the output stream, thus sending the response to the browser
     *
     * @return void
     * @throws RequestException
     */
    public function sendResponse(string $sCode = '200')
    {
        if(!$this->getConfigManager()->getOption('core.response.send', false))
        {
            return;
        }

        // Check to see if headers have already been sent out, in which case we can't do our job
        if(headers_sent($sFilename, $nLineNumber))
        {
            throw new RequestException($this->translator()->trans('errors.output.already-sent',
                    ['location' => $sFilename . ':' . $nLineNumber]) . "\n" .
                $this->translator()->trans('errors.output.advice'));
        }
        if(empty($sContent = $this->getResponseManager()->getOutput()))
        {
            return;
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

        if($this->getConfigManager()->getOption('core.process.exit', false))
        {
            exit();
        }
    }
}
