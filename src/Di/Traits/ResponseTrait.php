<?php

namespace Jaxon\Di\Traits;

use Jaxon\Config\ConfigManager;
use Jaxon\Di\Container;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Response\Response;
use Jaxon\Utils\Translation\Translator;
use Nyholm\Psr7\Factory\Psr17Factory;

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
        // Global Response
        $this->set(Response::class, function($c) {
            return new Response($c->g(Container::class), $c->g(PluginManager::class), $c->g(Psr17Factory::class));
        });
        // Response Manager
        $this->set(ResponseManager::class, function($c) {
            $sCharacterEncoding = trim($c->g(ConfigManager::class)->getOption('core.encoding', ''));
            return new ResponseManager($sCharacterEncoding, $c->g(Container::class), $c->g(Translator::class));
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
        return new Response($this->g(Container::class), $this->g(PluginManager::class),
            $this->g(Psr17Factory::class));
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
}
