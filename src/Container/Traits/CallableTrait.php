<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Container\Container;
use Jaxon\Request\Handler\RequestHandler;
use Jaxon\Request\Plugin\CallableClass\CallableRegistry;
use Jaxon\Request\Plugin\CallableClass\CallableRepository;
use Jaxon\Request\Plugin\CallableClass\CallableClassPlugin;
use Jaxon\Request\Plugin\CallableClass\CallableDirPlugin;
use Jaxon\Request\Plugin\CallableFunction\CallableFunctionPlugin;
use Jaxon\Request\Validator;
use Jaxon\Response\ResponseManager;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Template\Engine as TemplateEngine;
use Jaxon\Utils\Translation\Translator;

trait CallableTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerCallables()
    {
        // Validator
        $this->set(Validator::class, function($c) {
            return new Validator($c->g(Translator::class), $c->g(Config::class));
        });
        // Callable objects repository
        $this->set(CallableRepository::class, function() {
            return new CallableRepository($this);
        });
        // Callable objects registry
        $this->set(CallableRegistry::class, function($c) {
            return new CallableRegistry($this, $c->g(CallableRepository::class), $c->g(Translator::class));
        });
        // Callable class plugin
        $this->set(CallableClassPlugin::class, function($c) {
            return new CallableClassPlugin($c->g(Config::class), $c->g(RequestHandler::class),
                $c->g(ResponseManager::class), $c->g(CallableRegistry::class), $c->g(CallableRepository::class),
                $c->g(TemplateEngine::class), $c->g(Translator::class), $c->g(Validator::class));
        });
        // Callable dir plugin
        $this->set(CallableDirPlugin::class, function($c) {
            return new CallableDirPlugin($c->g(CallableRegistry::class), $c->g(Translator::class));
        });
        // Callable function plugin
        $this->set(CallableFunctionPlugin::class, function($c) {
            return new CallableFunctionPlugin($c->g(Container::class), $c->g(RequestHandler::class),
                $c->g(ResponseManager::class), $c->g(TemplateEngine::class),
                $c->g(Translator::class), $c->g(Validator::class));
        });
    }

    /**
     * Get the callable registry
     *
     * @return CallableRegistry
     */
    public function getClassRegistry(): CallableRegistry
    {
        return $this->g(CallableRegistry::class);
    }
}
