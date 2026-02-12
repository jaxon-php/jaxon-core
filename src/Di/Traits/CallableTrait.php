<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\ComponentContainer;
use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassPlugin;
use Jaxon\Plugin\Request\CallableClass\CallableDirPlugin;
use Jaxon\Plugin\Request\CallableClass\ComponentRegistry;
use Jaxon\Plugin\Request\CallableFunction\CallableFunctionPlugin;
use Jaxon\Request\Validator;
use Jaxon\Utils\Template\TemplateEngine;

trait CallableTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerCallables(): void
    {
        // Validator
        $this->set(Validator::class, fn(Container $di) =>
            new Validator($di->g(ConfigManager::class), $di->g(Translator::class)));
        // Callable objects registry
        $this->set(ComponentRegistry::class, fn(Container $di) =>
            new ComponentRegistry($di->g(ComponentContainer::class)));
        // Callable class plugin
        $this->set(CallableClassPlugin::class, function(Container $di) {
            $sPrefix = $di->g(ConfigManager::class)->getOption('core.prefix.class');
            return new CallableClassPlugin($sPrefix, $di->getLogger(),
                $di->g(ComponentContainer::class), $di->g(ComponentRegistry::class),
                $di->g(Translator::class), $di->g(TemplateEngine::class),
                $di->g(Validator::class));
        });
        // Callable dir plugin
        $this->set(CallableDirPlugin::class, fn(Container $di) =>
            new CallableDirPlugin($di->g(ComponentContainer::class),
                $di->g(ComponentRegistry::class), $di->g(Translator::class)));
        // Callable function plugin
        $this->set(CallableFunctionPlugin::class, function(Container $di) {
            $sPrefix = $di->g(ConfigManager::class)->getOption('core.prefix.function');
            $bDebug = $di->g(ConfigManager::class)->getOption('core.debug.on', false);
            return new CallableFunctionPlugin($sPrefix, $bDebug,
                $di->g(Container::class), $di->g(TemplateEngine::class),
                $di->g(Translator::class), $di->g(Validator::class));
        });
    }

    /**
     * Get the callable registry
     *
     * @return ComponentRegistry
     */
    public function getComponentRegistry(): ComponentRegistry
    {
        return $this->g(ComponentRegistry::class);
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
