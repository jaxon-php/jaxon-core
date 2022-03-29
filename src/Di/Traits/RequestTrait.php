<?php

namespace Jaxon\Di\Traits;

use Jaxon\Config\ConfigManager;
use Jaxon\Di\Container;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Request\Factory\Factory;
use Jaxon\Request\Factory\ParameterFactory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Request\Handler\RequestHandler;
use Jaxon\Request\Handler\UploadHandler;
use Jaxon\Request\Plugin\CallableClass\CallableRegistry;
use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Response\Plugin\DataBag\DataBagPlugin;
use Jaxon\Ui\Dialogs\DialogFacade;
use Jaxon\Ui\Pagination\Paginator;
use Jaxon\Utils\Http\UriDetector;
use Jaxon\Utils\Translation\Translator;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;

trait RequestTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerRequests()
    {
        // The server request
        $this->set(ServerRequestCreator::class, function() {
            $xRequestFactory = new Psr17Factory();
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
        // The parameter reader
        $this->set(ParameterReader::class, function($c) {
            return new ParameterReader($c->g(Container::class), $c->g(ConfigManager::class),
                $c->g(Translator::class), $c->g(UriDetector::class));
        });
        // Callback Manager
        $this->set(CallbackManager::class, function() {
            return new CallbackManager();
        });
        // Request Handler
        $this->set(RequestHandler::class, function($c) {
            return new RequestHandler($c->g(Container::class), $c->g(PluginManager::class),
                $c->g(ResponseManager::class), $c->g(CallbackManager::class),
                $c->g(UploadHandler::class), $c->g(DataBagPlugin::class));
        });
        // Request Factory
        $this->set(Factory::class, function($c) {
            return new Factory($c->g(CallableRegistry::class), $c->g(RequestFactory::class),
                $c->g(ParameterFactory::class));
        });
        // Factory for requests to functions
        $this->set(RequestFactory::class, function($c) {
            $sPrefix = $c->g(ConfigManager::class)->getOption('core.prefix.function');
            return new RequestFactory($sPrefix, $c->g(DialogFacade::class), $c->g(Paginator::class));
        });
        // Parameter Factory
        $this->set(ParameterFactory::class, function() {
            return new ParameterFactory();
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
     * Get the request handler
     *
     * @return RequestHandler
     */
    public function getRequestHandler(): RequestHandler
    {
        return $this->g(RequestHandler::class);
    }

    /**
     * Get the callback manager
     *
     * @return CallbackManager
     */
    public function getCallbackManager(): CallbackManager
    {
        return $this->g(CallbackManager::class);
    }

    /**
     * Get the parameter reader
     *
     * @return ParameterReader
     */
    public function getParameterReader(): ParameterReader
    {
        return $this->g(ParameterReader::class);
    }
}
