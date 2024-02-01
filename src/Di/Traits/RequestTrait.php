<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\Library\DialogLibraryManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\Request\CallableClass\CallableRegistry;
use Jaxon\Plugin\Response\DataBag\DataBagPlugin;
use Jaxon\Request\Call\Paginator;
use Jaxon\Request\Factory\Factory;
use Jaxon\Request\Factory\ParameterFactory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Request\Handler\RequestHandler;
use Jaxon\Request\Upload\UploadHandlerInterface;
use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Utils\Http\UriDetector;

trait RequestTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerRequests()
    {
        // The parameter reader
        $this->set(ParameterReader::class, function($di) {
            return new ParameterReader($di->g(Container::class), $di->g(ConfigManager::class),
                $di->g(Translator::class), $di->g(UriDetector::class));
        });
        // Callback Manager
        $this->set(CallbackManager::class, function($di) {
            return new CallbackManager($di->g(ResponseManager::class));
        });
        // By default, register a null upload handler
        $this->set(UploadHandlerInterface::class, function() {
            return null;
        });
        // Request Handler
        $this->set(RequestHandler::class, function($di) {
            return new RequestHandler($di->g(Container::class), $di->g(PluginManager::class),
                $di->g(ResponseManager::class), $di->g(CallbackManager::class), $di->g(DataBagPlugin::class));
        });
        // Request Factory
        $this->set(Factory::class, function($di) {
            return new Factory($di->g(CallableRegistry::class), $di->g(RequestFactory::class),
                $di->g(ParameterFactory::class));
        });
        // Factory for requests to functions
        $this->set(RequestFactory::class, function($di) {
            $sPrefix = $di->g(ConfigManager::class)->getOption('core.prefix.function');
            return new RequestFactory($sPrefix, $di->g(DialogLibraryManager::class), $di->g(Paginator::class));
        });
        // Parameter Factory
        $this->set(ParameterFactory::class, function() {
            return new ParameterFactory();
        });
    }

    /**
     * Get the factory
     *
     * @return Factory
     */
    public function getFactory(): Factory
    {
        return $this->g(Factory::class);
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
     * Get the upload handler
     *
     * @return UploadHandlerInterface|null
     */
    public function getUploadHandler(): ?UploadHandlerInterface
    {
        return $this->g(UploadHandlerInterface::class);
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
