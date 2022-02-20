<?php

namespace Jaxon\Container\Traits;

use Jaxon\Utils\Config\Config;
use Jaxon\Utils\File\Minifier;
use Jaxon\Utils\Http\URI;
use Jaxon\Utils\Template\Engine as TemplateEngine;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Utils\Validation\Validator;
use Lemon\Event\EventDispatcher;

trait UtilTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerUtils()
    {
        // Translator
        $this->set(Translator::class, function($c) {
            return new Translator($c->g('jaxon.core.dir.translation'), $c->g(Config::class));
        });
        // Validator
        $this->set(Validator::class, function($c) {
            return new Validator($c->g(Translator::class), $c->g(Config::class));
        });
        // Template engine
        $this->set(TemplateEngine::class, function($c) {
            return new TemplateEngine($c->g('jaxon.core.dir.template'));
        });
        // Minifier
        $this->set(Minifier::class, function() {
            return new Minifier();
        });
        // Event Dispatcher
        $this->set(EventDispatcher::class, function() {
            return new EventDispatcher();
        });
        // URI decoder
        $this->set(URI::class, function() {
            return new URI();
        });
    }

    /**
     * Get the translator
     *
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->g(Translator::class);
    }

    /**
     * Get the validator
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->g(Validator::class);
    }

    /**
     * Get the template engine
     *
     * @return TemplateEngine
     */
    public function getTemplateEngine()
    {
        return $this->g(TemplateEngine::class);
    }

    /**
     * Get the minifier
     *
     * @return Minifier
     */
    public function getMinifier()
    {
        return $this->g(Minifier::class);
    }

    /**
     * Get the event dispatcher
     *
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->g(EventDispatcher::class);
    }
}
