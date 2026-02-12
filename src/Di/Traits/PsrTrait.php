<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Plugin\Response\Psr\PsrPlugin;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Request\Handler\Psr\PsrAjaxMiddleware;
use Jaxon\Request\Handler\Psr\PsrConfigMiddleware;
use Jaxon\Request\Handler\Psr\PsrFactory;
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
     * @var string
     */
    private $sPsrServerRequest = 'jaxon.psr.server.request';

    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerPsr(): void
    {
        // The server request
        $this->set(Psr17Factory::class, fn() => new Psr17Factory());
        $this->set(ServerRequestCreator::class, function(Container $di) {
            $xPsr17Factory = $di->g(Psr17Factory::class);
            return new ServerRequestCreator(
                $xPsr17Factory, // ServerRequestFactory
                $xPsr17Factory, // UriFactory
                $xPsr17Factory, // UploadedFileFactory
                $xPsr17Factory, // StreamFactory
            );
        });
        $this->set(ServerRequestInterface::class, fn(Container $di) =>
            $di->g(ServerRequestCreator::class)->fromGlobals());
        // Server request with the Jaxon request parameter as attribute
        $this->set($this->sPsrServerRequest, function(Container $di) {
            /** @var ParameterReader */
            $xParameterReader = $di->g(ParameterReader::class);
            return $xParameterReader->setRequestParameter($di->g(ServerRequestInterface::class));
        });

        // PSR factory
        $this->set(PsrFactory::class, fn(Container $di) => new PsrFactory($di->g(Container::class)));
        // PSR request handler
        $this->set(PsrRequestHandler::class, fn(Container $di) =>
            new PsrRequestHandler($di->g(Container::class), $di->g(RequestHandler::class),
                $di->g(ResponseManager::class), $di->g(Translator::class)));
        // PSR config middleware
        $this->set(PsrConfigMiddleware::class, fn(Container $di) =>
            new PsrConfigMiddleware($di->g(Container::class), $di->g($this->sPsrConfig)));
        // PSR ajax middleware
        $this->set(PsrAjaxMiddleware::class, fn(Container $di) =>
            new PsrAjaxMiddleware($di->g(Container::class),
                $di->g(RequestHandler::class), $di->g(ResponseManager::class)));
        // The PSR response plugin
        $this->set(PsrPlugin::class, fn(Container $di) =>
            new PsrPlugin($di->g(Psr17Factory::class), $di->g(ServerRequestInterface::class)));
    }

    /**
     * Get the request
     *
     * @return array
     */
    public function getServerParams(): array
    {
        $xRequest = $this->g(ServerRequestInterface::class);
        return $xRequest->getServerParams();
    }

    /**
     * Get the request with Jaxon parameter as attribute
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->g($this->sPsrServerRequest);
    }

    /**
     * Return the array of arguments from the GET or POST data
     *
     * @return array
     */
    public function getRequestArguments(): array
    {
        return $this->getRequest()->getAttribute('jxncall')['args'] ?? [];
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
     * Get the Psr17 factory
     *
     * @return Psr17Factory
     */
    public function getPsr17Factory(): Psr17Factory
    {
        return $this->g(Psr17Factory::class);
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
        !$this->h($this->sPsrConfig) && $this->val($this->sPsrConfig, $sConfigFile);
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
