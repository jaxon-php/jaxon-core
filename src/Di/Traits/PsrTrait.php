<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Request\Factory\Psr\PsrFactory;
use Jaxon\Request\Handler\Psr\PsrAjaxMiddleware;
use Jaxon\Request\Handler\Psr\PsrConfigMiddleware;
use Jaxon\Request\Handler\Psr\PsrRequestHandler;
use Jaxon\Request\Handler\RequestHandler;
use Jaxon\Response\Manager\ResponseManager;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;

trait PsrTrait
{
    /**
     * @var string
     */
    private $sPsrConfig = 'jaxon.psr.config.file';

    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerPsr()
    {
        // The server request
        $this->set(Psr17Factory::class, function() {
            return new Psr17Factory();
        });
        $this->set(ServerRequestCreator::class, function($c) {
            $xRequestFactory = $c->g(Psr17Factory::class);
            return new ServerRequestCreator(
                $xRequestFactory, // ServerRequestFactory
                $xRequestFactory, // UriFactory
                $xRequestFactory, // UploadedFileFactory
                $xRequestFactory  // StreamFactory
            );
        });
        $this->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals();
        });
        // PSR factory
        $this->set(PsrFactory::class, function($c) {
            return new PsrFactory($c->g(Container::class));
        });
        // PSR request handler
        $this->set(PsrRequestHandler::class, function($c) {
            return new PsrRequestHandler($c->g(Container::class), $c->g(RequestHandler::class),
                $c->g(ResponseManager::class), $c->g(Translator::class));
        });
        // PSR config middleware
        $this->set(PsrConfigMiddleware::class, function($c) {
            return new PsrConfigMiddleware($c->g(Container::class), $c->g($this->sPsrConfig));
        });
        // PSR ajax middleware
        $this->set(PsrAjaxMiddleware::class, function($c) {
            return new PsrAjaxMiddleware($c->g(Container::class), $c->g(RequestHandler::class),
                $c->g(ResponseManager::class));
        });
    }

    /**
     * Get the request
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->g(ServerRequestInterface::class);
    }

    /**
     * Get the PSR factory
     *
     * @return PsrFactory
     */
    public function getPsrFactory(): PsrFactory
    {
        return $this->g(PsrFactory::class);
    }

    /**
     * Get the PSR request handler
     *
     * @return PsrRequestHandler
     */
    public function getPsrRequestHandler(): PsrRequestHandler
    {
        return $this->g(PsrRequestHandler::class);
    }

    /**
     * Get the PSR config middleware
     *
     * @param string $sConfigFile
     *
     * @return PsrConfigMiddleware
     */
    public function getPsrConfigMiddleware(string $sConfigFile): PsrConfigMiddleware
    {
        $this->val($this->sPsrConfig, $sConfigFile);
        return $this->g(PsrConfigMiddleware::class);
    }

    /**
     * Get the PSR ajax request middleware
     *
     * @return PsrAjaxMiddleware
     */
    public function getPsrAjaxMiddleware(): PsrAjaxMiddleware
    {
        return $this->g(PsrAjaxMiddleware::class);
    }
}
