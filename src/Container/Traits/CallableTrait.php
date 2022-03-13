<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Plugin\Code\Generator as CodeGenerator;
use Jaxon\Request\Handler\Handler as RequestHandler;
use Jaxon\Request\Plugin\CallableClass\CallableRegistry;
use Jaxon\Request\Plugin\CallableClass\CallableRepository;
use Jaxon\Request\Plugin\CallableClass\ClassPlugin;
use Jaxon\Request\Plugin\CallableClass\DirPlugin;
use Jaxon\Request\Plugin\CallableFunction\FunctionPlugin;
use Jaxon\Request\Validator;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\File\Minifier;
use Jaxon\Utils\Http\UriDetector;
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
            return new CallableRegistry($this, $c->g(CallableRepository::class));
        });
        // Callable class plugin
        $this->set(ClassPlugin::class, function($c) {
            return new ClassPlugin($c->g(Config::class), $c->g(RequestHandler::class),
                $c->g(ResponseManager::class), $c->g(CallableRegistry::class), $c->g(CallableRepository::class),
                $c->g(TemplateEngine::class), $c->g(Translator::class), $c->g(Validator::class));
        });
        // Callable dir plugin
        $this->set(DirPlugin::class, function($c) {
            return new DirPlugin($c->g(CallableRegistry::class), $c->g(Translator::class));
        });
        // Callable function plugin
        $this->set(FunctionPlugin::class, function($c) {
            return new FunctionPlugin($c->g(Jaxon::class), $c->g(Config::class),
                $c->g(RequestHandler::class), $c->g(ResponseManager::class),
                $c->g(TemplateEngine::class), $c->g(Translator::class), $c->g(Validator::class));
        });
        // Code Generator
        $this->set(CodeGenerator::class, function($c) {
            return new CodeGenerator($c->g(Jaxon::class), $c->g(Config::class),
                $c->g(UriDetector::class), $c->g(TemplateEngine::class), $c->g(Minifier::class));
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
     * Get the code generator
     *
     * @return CodeGenerator
     */
    public function getCodeGenerator(): CodeGenerator
    {
        return $this->g(CodeGenerator::class);
    }
}
