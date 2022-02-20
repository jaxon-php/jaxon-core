<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Response\Response;
use Jaxon\Response\Manager as ResponseManager;

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
            return new ResponseManager($c->g(Jaxon::class));
        });
    }

    /**
     * Get the response manager
     *
     * @return ResponseManager
     */
    public function getResponseManager()
    {
        return $this->g(ResponseManager::class);
    }

    /**
     * Get the global Response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->g(Response::class);
    }

    /**
     * Create a new Jaxon response object
     *
     * @return Response
     */
    public function newResponse()
    {
        return new Response();
    }
}
