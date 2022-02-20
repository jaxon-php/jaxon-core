<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Request\Factory\ParameterFactory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\Handler as RequestHandler;
use Jaxon\Request\Plugin\FileUpload;
use Jaxon\Request\Support\CallableRegistry;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Response\Plugin\DataBag;

trait RequestTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerRequests()
    {
        // Request Handler
        $this->set(RequestHandler::class, function($c) {
            return new RequestHandler($c->g(Jaxon::class), $c->g(PluginManager::class),
                $c->g(ResponseManager::class), $c->g(FileUpload::class), $c->g(DataBag::class));
        });
        // Request Factory
        $this->set(RequestFactory::class, function($c) {
            return new RequestFactory($c->g(CallableRegistry::class));
        });
        // Parameter Factory
        $this->set(ParameterFactory::class, function() {
            return new ParameterFactory();
        });
    }

    /**
     * Get the request handler
     *
     * @return RequestHandler
     */
    public function getRequestHandler()
    {
        return $this->g(RequestHandler::class);
    }

    /**
     * Get the request factory
     *
     * @return RequestFactory
     */
    public function getRequestFactory()
    {
        return $this->g(RequestFactory::class);
    }

    /**
     * Get the parameter factory
     *
     * @return ParameterFactory
     */
    public function getParameterFactory()
    {
        return $this->g(ParameterFactory::class);
    }
}
