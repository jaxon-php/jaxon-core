<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Plugin\Code\Generator as CodeGenerator;
use Jaxon\Request\Handler\Handler as RequestHandler;
use Jaxon\Request\Plugin\CallableClass;
use Jaxon\Request\Plugin\CallableDir;
use Jaxon\Request\Plugin\CallableFunction;
use Jaxon\Request\Support\CallableRegistry;
use Jaxon\Request\Support\CallableRepository;
use Jaxon\Request\Validator;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Utils\Config\Config;
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
            return new CallableRegistry($c->g(CallableRepository::class));
        });
        // Callable class plugin
        $this->set(CallableClass::class, function($c) {
            return new CallableClass($c->g(RequestHandler::class),
                $c->g(ResponseManager::class), $c->g(CallableRegistry::class),
                $c->g(CallableRepository::class), $c->g(Validator::class));
        });
        // Callable dir plugin
        $this->set(CallableDir::class, function($c) {
            return new CallableDir($c->g(CallableRegistry::class));
        });
        // Callable function plugin
        $this->set(CallableFunction::class, function($c) {
            return new CallableFunction($this, $c->g(RequestHandler::class),
                $c->g(ResponseManager::class), $c->g(Validator::class));
        });
        // Code Generator
        $this->set(CodeGenerator::class, function($c) {
            return new CodeGenerator($c->g(Jaxon::class), $c->g(UriDetector::class), $c->g(TemplateEngine::class));
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
