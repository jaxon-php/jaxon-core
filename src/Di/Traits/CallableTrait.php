<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\ComponentContainer;
use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableComponent\ComponentPlugin;
use Jaxon\Plugin\Request\CallableComponent\DirectoryPlugin;
use Jaxon\Plugin\Request\CallableComponent\ComponentRegistry;
use Jaxon\Plugin\Request\CallableFunction\FunctionPlugin;
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
        $this->set(ComponentPlugin::class, function(Container $di) {
            $sPrefix = $di->g(ConfigManager::class)->getOption('core.prefix.class');
            $bDebug = $di->g(ConfigManager::class)->getOption('core.debug.on', false);
            return new ComponentPlugin($sPrefix, $bDebug,
                $di->g(ComponentContainer::class), $di->getLogger(),
                $di->g(ComponentRegistry::class), $di->g(Translator::class),
                $di->g(TemplateEngine::class), $di->g(Validator::class));
        });
        // Callable dir plugin
        $this->set(DirectoryPlugin::class, fn(Container $di) =>
            new DirectoryPlugin($di->g(ComponentContainer::class),
                $di->g(ComponentRegistry::class), $di->g(Translator::class)));
        // Callable function plugin
        $this->set(FunctionPlugin::class, function(Container $di) {
            $sPrefix = $di->g(ConfigManager::class)->getOption('core.prefix.function');
            $bDebug = $di->g(ConfigManager::class)->getOption('core.debug.on', false);
            return new FunctionPlugin($sPrefix, $bDebug,
                $di->g(ComponentContainer::class), $di->getLogger(),
                $di->g(Translator::class), $di->g(Validator::class),
                $di->g(TemplateEngine::class));
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
     * @return FunctionPlugin
     */
    public function getFunctionPlugin(): FunctionPlugin
    {
        return $this->g(FunctionPlugin::class);
    }

    /**
     * Get the callable class plugin
     *
     * @return ComponentPlugin
     */
    public function getComponentPlugin(): ComponentPlugin
    {
        return $this->g(ComponentPlugin::class);
    }

    /**
     * Get the callable dir plugin
     *
     * @return DirectoryPlugin
     */
    public function getDirectoryPlugin(): DirectoryPlugin
    {
        return $this->g(DirectoryPlugin::class);
    }
}
