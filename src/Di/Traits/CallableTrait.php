<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\App\Pagination;
use Jaxon\Di\ClassContainer;
use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassPlugin;
use Jaxon\Plugin\Request\CallableClass\CallableDirPlugin;
use Jaxon\Plugin\Request\CallableClass\CallableRegistry;
use Jaxon\Plugin\Request\CallableFunction\CallableFunctionPlugin;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Request\Validator;
use Jaxon\Utils\Template\TemplateEngine;

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
        $this->set(Validator::class, function($di) {
            return new Validator($di->g(ConfigManager::class), $di->g(Translator::class));
        });
        // Callable objects registry
        $this->set(CallableRegistry::class, function($di) {
            $xRegistry = new CallableRegistry($di->g(ClassContainer::class));
            // Register the pagination component, but do not export to js.
            $xRegistry->addClass(Pagination::class,
                ['excluded' => true, 'namespace' => 'Jaxon\App']);
            return $xRegistry;
        });
        // Callable class plugin
        $this->set(CallableClassPlugin::class, function($di) {
            $sPrefix = $di->g(ConfigManager::class)->getOption('core.prefix.class');
            return new CallableClassPlugin($sPrefix, $di->g(Container::class),
                $di->g(ClassContainer::class), $di->g(ParameterReader::class),
                $di->g(CallableRegistry::class), $di->g(TemplateEngine::class),
                $di->g(Translator::class), $di->g(Validator::class));
        });
        // Callable dir plugin
        $this->set(CallableDirPlugin::class, function($di) {
            return new CallableDirPlugin($di->g(ClassContainer::class),
                $di->g(CallableRegistry::class), $di->g(Translator::class));
        });
        // Callable function plugin
        $this->set(CallableFunctionPlugin::class, function($di) {
            $sPrefix = $di->g(ConfigManager::class)->getOption('core.prefix.function');
            return new CallableFunctionPlugin($sPrefix, $di->g(Container::class), $di->g(ParameterReader::class),
                $di->g(TemplateEngine::class), $di->g(Translator::class), $di->g(Validator::class));
        });
    }

    /**
     * Get the callable registry
     *
     * @return CallableRegistry
     */
    public function getCallableRegistry(): CallableRegistry
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
