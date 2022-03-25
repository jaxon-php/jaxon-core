<?php

namespace Jaxon\Di\Traits;

use Jaxon\Config\ConfigManager;
use Jaxon\Di\Container;
use Jaxon\Request\Handler\ArgumentManager;
use Jaxon\Request\Plugin\CallableClass\CallableRegistry;
use Jaxon\Request\Plugin\CallableClass\CallableRepository;
use Jaxon\Request\Plugin\CallableClass\CallableClassPlugin;
use Jaxon\Request\Plugin\CallableClass\CallableDirPlugin;
use Jaxon\Request\Plugin\CallableFunction\CallableFunctionPlugin;
use Jaxon\Request\Validator;
use Jaxon\Response\ResponseManager;
use Jaxon\Utils\Template\TemplateEngine;
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
            return new Validator($c->g(ConfigManager::class), $c->g(Translator::class));
        });
        // Callable objects repository
        $this->set(CallableRepository::class, function($c) {
            return new CallableRepository($c->g(Container::class));
        });
        // Callable objects registry
        $this->set(CallableRegistry::class, function($c) {
            return new CallableRegistry($c->g(Container::class),
                $c->g(CallableRepository::class), $c->g(Translator::class));
        });
        // Callable class plugin
        $this->set(CallableClassPlugin::class, function($c) {
            $sPrefix = $c->g(ConfigManager::class)->getOption('core.prefix.class');
            return new CallableClassPlugin($sPrefix, $c->g(ArgumentManager::class),
                $c->g(ResponseManager::class), $c->g(CallableRegistry::class), $c->g(CallableRepository::class),
                $c->g(TemplateEngine::class), $c->g(Translator::class), $c->g(Validator::class));
        });
        // Callable dir plugin
        $this->set(CallableDirPlugin::class, function($c) {
            return new CallableDirPlugin($c->g(CallableRegistry::class), $c->g(Translator::class));
        });
        // Callable function plugin
        $this->set(CallableFunctionPlugin::class, function($c) {
            $sPrefix = $c->g(ConfigManager::class)->getOption('core.prefix.function');
            return new CallableFunctionPlugin($sPrefix, $c->g(ArgumentManager::class),
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

    /**
     * Get the callable function plugin
     *
     * @return CallableFunctionPlugin
     */
    public function getCallableFunctionPlugin(): CallableFunctionPlugin
    {
        return $this->g(CallableFunctionPlugin::class);
    }

    /**
     * Get the callable class plugin
     *
     * @return CallableClassPlugin
     */
    public function getCallableClassPlugin(): CallableClassPlugin
    {
        return $this->g(CallableClassPlugin::class);
    }

    /**
     * Get the callable dir plugin
     *
     * @return CallableDirPlugin
     */
    public function getCallableDirPlugin(): CallableDirPlugin
    {
        return $this->g(CallableDirPlugin::class);
    }
}
