<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Plugin\PluginManager;
use Jaxon\Request\Handler\ArgumentManager;
use Jaxon\Response\Response;
use Jaxon\Response\ResponseManager;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Translation\Translator;

trait ResponseTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerResponses()
    {
        /*
         * Core library objects
         */
        // Global Response
        $this->set(Response::class, function($c) {
            return new Response($c->g(Config::class),
                $c->g(Translator::class), $c->g(PluginManager::class));
        });
        // Response Manager
        $this->set(ResponseManager::class, function($c) {
            return new ResponseManager($c->g(Jaxon::class), $c->g(Config::class),
                $c->g(ArgumentManager::class), $c->g(Translator::class));
        });
    }

    /**
     * Get the response manager
     *
     * @return ResponseManager
     */
    public function getResponseManager(): ResponseManager
    {
        return $this->g(ResponseManager::class);
    }

    /**
     * Get the global Response object
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->g(Response::class);
    }

    /**
     * Create a new Jaxon response object
     *
     * @return Response
     */
    public function newResponse(): Response
    {
        return new Response($this->g(Config::class),
            $this->g(Translator::class), $this->g(PluginManager::class));
    }
}
