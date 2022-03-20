<?php

namespace Jaxon\Container\Traits;

use Jaxon\Config\ConfigManager;
use Jaxon\Container\Container;
use Jaxon\Plugin\PluginManager;
use Jaxon\Request\Handler\ArgumentManager;
use Jaxon\Response\Response;
use Jaxon\Response\ResponseManager;
use Jaxon\Utils\Translation\Translator;

use function trim;

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
            return new Response($c->g(Translator::class), $c->g(PluginManager::class));
        });
        // Response Manager
        $this->set(ResponseManager::class, function($c) {
            $sCharacterEncoding = trim($c->g(ConfigManager::class)->getOption('core.encoding', ''));
            return new ResponseManager($sCharacterEncoding, $c->g(Container::class),
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
        return new Response($this->g(Translator::class), $this->g(PluginManager::class));
    }
}
