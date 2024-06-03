<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\DialogManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\JsCall\Factory;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Response\Response;
use Jaxon\Response\ComponentResponse;
use Jaxon\Response\ResponseManager;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;

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
        $this->set(Response::class, function($di) {
            return new Response($di->g(ResponseManager::class),
                $di->g(Psr17Factory::class), $di->g(ServerRequestInterface::class),
                $di->g(PluginManager::class), $di->g(DialogManager::class));
        });
        // Response Manager
        $this->set(ResponseManager::class, function($di) {
            return new ResponseManager($di->g(Container::class), $di->g(Translator::class),
                trim($di->g(ConfigManager::class)->getOption('core.encoding', '')));
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
        return new Response($this->g(ResponseManager::class),
            $this->g(Psr17Factory::class), $this->g(ServerRequestInterface::class),
            $this->g(PluginManager::class), $this->g(DialogManager::class));
    }

    /**
     * Create a new reponse for a Jaxon component
     *
     * @param string $sComponentClass
     *
     * @return ComponentResponse
     */
    public function newComponentResponse(string $sComponentClass): ComponentResponse
    {
        /** @var Factory */
        $xFactory = $this->g(Factory::class);
        $sComponentName = $xFactory->rq($sComponentClass)->_class();
        return new ComponentResponse($this->g(ResponseManager::class),
            $this->g(Psr17Factory::class), $this->g(ServerRequestInterface::class),
            $this->g(PluginManager::class), $this->g(DialogManager::class), $sComponentName);
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
