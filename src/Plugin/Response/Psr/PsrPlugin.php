<?php

/**
 * PaginatorPlugin.php
 *
 * A plugin to convert a response content to PSR.
 *
 * @package jaxon-core
 * @copyright 2024 Thierry Feuzeu
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Response\Psr;

use Jaxon\Plugin\ResponsePlugin;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;

use function gmdate;

class PsrPlugin extends ResponsePlugin
{
    /**
     * @const The plugin name
     */
    const NAME = 'psr';

    /**
     * @var Psr17Factory
     */
    private $xPsr17Factory;

    /**
     * @var RequestInterface
     */
    protected $xRequest;

    /**
     * The class constructor
     *
     * @param Psr17Factory $xPsr17Factory
     * @param RequestInterface $xRequest
     */
    public function __construct(Psr17Factory $xPsr17Factory, RequestInterface $xRequest)
    {
        $this->xPsr17Factory = $xPsr17Factory;
        $this->xRequest = $xRequest;
    }

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
    public function ajax(): ResponseInterface
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
            ->withHeader('content-type', $this->xResponse->getContentType())
            ->withBody(Stream::create($this->xResponse->getOutput()));
    }

    /**
     * Convert an upload response to a PSR7 response object
     *
     * @param int $nHttpCode The response HTTP code
     *
     * @return ResponseInterface
     */
    public function upload(int $nHttpCode): ResponseInterface
    {
        return $this->xPsr17Factory->createResponse($nHttpCode)
            ->withHeader('content-type', $this->xResponse->getContentType())
            ->withBody(Stream::create($this->xResponse->getOutput()));
    }
}
