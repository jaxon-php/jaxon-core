<?php

namespace Jaxon\Container\Traits;

use Jaxon\Contracts\Session as SessionContract;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Http\URI;
use Jaxon\Utils\Pagination\Paginator;
use Jaxon\Utils\Pagination\Renderer as PaginationRenderer;
use Jaxon\Utils\Session\Manager as SessionManager;
use Jaxon\Utils\Template\Minifier;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Utils\Validation\Validator;
use Jaxon\Utils\View\Renderer as ViewRenderer;

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
        // Minifier
        $this->set(Minifier::class, function() {
            return new Minifier();
        });
        // Translator
        $this->set(Translator::class, function($c) {
            return new Translator($c->g('jaxon.core.dir.translation'), $c->g(Config::class));
        });
        // Validator
        $this->set(Validator::class, function($c) {
            return new Validator($c->g(Translator::class), $c->g(Config::class));
        });
        // Pagination Paginator
        $this->set(Paginator::class, function($c) {
            return new Paginator($c->g(PaginationRenderer::class));
        });
        // Pagination Renderer
        $this->set(PaginationRenderer::class, function($c) {
            return new PaginationRenderer($c->g(ViewRenderer::class));
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
     * Get the minifier
     *
     * @return Minifier
     */
    public function getMinifier()
    {
        return $this->g(Minifier::class);
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
     * Get the paginator
     *
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->g(Paginator::class);
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
