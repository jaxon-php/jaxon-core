<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Response\Response;
use Jaxon\Response\NodeResponse;
use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Script\Call\JxnCall;

use function trim;

trait ResponseTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerResponses(): void
    {
        // Global Response
        $this->set(Response::class, fn($di) =>
            new Response($di->g(ResponseManager::class), $di->g(PluginManager::class)));
        // Response Manager
        $this->set(ResponseManager::class, function($di) {
            $sEncoding = trim($di->g(ConfigManager::class)->getOption('core.encoding', ''));
            return new ResponseManager($di->g(Container::class), $di->g(Translator::class), $sEncoding);
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
     * Create a new Jaxon response
     *
     * @return Response
     */
    public function newResponse(): Response
    {
        return new Response($this->g(ResponseManager::class), $this->g(PluginManager::class));
    }

    /**
     * Create a new reponse for a Jaxon component
     *
     * @param JxnCall $xJxnCall
     *
     * @return NodeResponse
     */
    public function newNodeResponse(JxnCall $xJxnCall): NodeResponse
    {
        return new NodeResponse($this->g(ResponseManager::class),
            $this->g(PluginManager::class), $xJxnCall);
    }
}
