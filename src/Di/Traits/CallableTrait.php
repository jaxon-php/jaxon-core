<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Plugin\AnnotationReaderInterface;
use Jaxon\Plugin\Request\CallableClass\CallableClassPlugin;
use Jaxon\Plugin\Request\CallableClass\CallableRegistry;
use Jaxon\Plugin\Request\CallableClass\CallableRepository;
use Jaxon\Plugin\Request\CallableDir\CallableDirPlugin;
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
        // By default, register a fake annotation reader.
        $this->set(AnnotationReaderInterface::class, function() {
            return new class implements AnnotationReaderInterface
            {
                public function getAttributes(string $sClass, array $aMethods = [], array $aProperties = []): array
                {
                    return [false, [], []];
                }
            };
        });
        // Validator
        $this->set(Validator::class, function($di) {
            return new Validator($di->g(ConfigManager::class), $di->g(Translator::class));
        });
        // Callable objects repository
        $this->set(CallableRepository::class, function($di) {
            return new CallableRepository($di->g(Container::class), $di->g(Translator::class));
        });
        // Callable objects registry
        $this->set(CallableRegistry::class, function($di) {
            return new CallableRegistry($di->g(Container::class),
                $di->g(CallableRepository::class), $di->g(Translator::class));
        });
        // Callable class plugin
        $this->set(CallableClassPlugin::class, function($di) {
            $sPrefix = $di->g(ConfigManager::class)->getOption('core.prefix.class');
            return new CallableClassPlugin($sPrefix, $di->g(Container::class), $di->g(ParameterReader::class),
                $di->g(CallableRegistry::class), $di->g(CallableRepository::class),
                $di->g(TemplateEngine::class), $di->g(Translator::class), $di->g(Validator::class));
        });
        // Callable dir plugin
        $this->set(CallableDirPlugin::class, function($di) {
            return new CallableDirPlugin($di->g(CallableRegistry::class), $di->g(Translator::class));
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
     * Get the callable repository
     *
     * @return CallableRepository
     */
    public function getCallableRepository(): CallableRepository
    {
        return $this->g(CallableRepository::class);
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
