<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Request\Handler\Argument as RequestArgument;
use Jaxon\Response\Response;
use Jaxon\Response\Manager as ResponseManager;
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
        $this->set(Response::class, function() {
            return new Response();
        });
        // Response Manager
        $this->set(ResponseManager::class, function($c) {
            return new ResponseManager($c->g(Jaxon::class), $c->g(Config::class),
                $c->g(RequestArgument::class), $c->g(Translator::class));
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
        return new Response();
    }
}
