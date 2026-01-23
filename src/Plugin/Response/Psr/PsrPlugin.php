<?php

/**
 * PsrPlugin.php
 *
 * A plugin to convert a response content to PSR.
 *
 * @package jaxon-core
 * @copyright 2024 Thierry Feuzeu
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Response\Psr;

use Jaxon\Plugin\AbstractResponsePlugin;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;

use function gmdate;

class PsrPlugin extends AbstractResponsePlugin
{
    /**
     * @const The plugin name
     */
    public const NAME = 'psr';

    /**
     * The class constructor
     *
     * @param Psr17Factory $xPsr17Factory
     * @param RequestInterface $xRequest
     */
    public function __construct(private Psr17Factory $xPsr17Factory,
        private RequestInterface $xRequest)
    {}

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        // Use the version number as hash
        return '5.0.0';
    }

    /**
     * Convert an ajax response to a PSR7 response object
     *
     * @return ResponseInterface
     */
    public function ajaxResponse(): ResponseInterface
    {
        $xPsrResponse = $this->xPsr17Factory->createResponse(200);
        if($this->xRequest->getMethod() === 'GET')
        {
            $xPsrResponse = $xPsrResponse
                ->withHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT')
                ->withHeader('Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT')
                ->withHeader('Cache-Control', 'no-cache, must-revalidate')
                ->withHeader('Pragma', 'no-cache');
        }
        return $xPsrResponse
            ->withHeader('content-type', $this->response()->getContentType())
            ->withBody(Stream::create($this->response()->getOutput()));
    }
}
