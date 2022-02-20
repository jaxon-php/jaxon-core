<?php

namespace Jaxon\Container\Traits;

use Jaxon\Utils\Dialogs\Dialog;
use Jaxon\Utils\Template\Engine as TemplateEngine;
use Jaxon\Utils\Template\View as TemplateView;
use Jaxon\Utils\View\Manager as ViewManager;
use Jaxon\Utils\View\Renderer as ViewRenderer;

trait ViewTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerViews()
    {
        // Dialog
        $this->set(Dialog::class, function() {
            return new Dialog();
        });
        // Template engine
        $this->set(TemplateEngine::class, function($c) {
            return new TemplateEngine($c->g('jaxon.core.dir.template'));
        });
        // View Manager
        $this->set(ViewManager::class, function() {
            $xViewManager = new ViewManager($this);
            // Add the default view renderer
            $xViewManager->addRenderer('jaxon', function($di) {
                return new TemplateView($di->get(TemplateEngine::class));
            });
            // By default, render pagination templates with Jaxon.
            $xViewManager->addNamespace('pagination', '', '.php', 'jaxon');
            return $xViewManager;
        });
        // View Renderer
        $this->set(ViewRenderer::class, function($c) {
            return new ViewRenderer($c->g(ViewManager::class));
        });
    }

    /**
     * Get the dialog wrapper
     *
     * @return Dialog
     */
    public function getDialog()
    {
        return $this->g(Dialog::class);
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
     * Get the view manager
     *
     * @return ViewManager
     */
    public function getViewManager()
    {
        return $this->g(ViewManager::class);
    }

    /**
     * Get the view facade
     *
     * @return ViewRenderer
     */
    public function getViewRenderer()
    {
        return $this->g(ViewRenderer::class);
    }
}
