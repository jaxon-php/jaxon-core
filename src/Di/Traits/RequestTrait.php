<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\DialogManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\JsCall\AttrFormatter;
use Jaxon\JsCall\Factory;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\Request\CallableClass\CallableRegistry;
use Jaxon\Plugin\Response\DataBag\DataBagPlugin;
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
                $di->g(ResponseManager::class), $di->g(CallbackManager::class),
                $di->g(DataBagPlugin::class));
        });
        // Requests and calls Factory
        $this->set(Factory::class, function($di) {
            $xConfigManager = $di->g(ConfigManager::class);
            return new Factory($di->g(CallableRegistry::class), $di->g(DialogManager::class),
                $xConfigManager->getOption('core.prefix.class'),
                $xConfigManager->getOption('core.prefix.function'));
        });
        // Helpers for HTML custom attributes formatting
        $this->set(AttrFormatter::class, function() {
            return new AttrFormatter();
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

    /**
     * Get the custom attributes formatter
     *
     * @return AttrFormatter
     */
    public function getCustomAttrFormatter(): AttrFormatter
    {
        return $this->g(AttrFormatter::class);
    }
}
