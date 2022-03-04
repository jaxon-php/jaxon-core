<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Ui\Dialogs\Dialog;
use Jaxon\Ui\Pagination\Paginator;
use Jaxon\Ui\Pagination\Renderer as PaginationRenderer;
use Jaxon\Ui\Template\View as TemplateView;
use Jaxon\Ui\View\Manager as ViewManager;
use Jaxon\Ui\View\Renderer as ViewRenderer;
use Jaxon\Utils\Template\Engine as TemplateEngine;

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
        $this->set(Dialog::class, function($c) {
            return new Dialog($c->g(Jaxon::class));
        });
        // View Manager
        $this->set(ViewManager::class, function() {
            $xViewManager = new ViewManager($this);
            // Add the default view renderer
            $xViewManager->addRenderer('jaxon', function($di) {
                return new TemplateView($di->g(TemplateEngine::class));
            });
            // By default, render pagination templates with Jaxon.
            $xViewManager->addNamespace('pagination', '', '.php', 'jaxon');
            return $xViewManager;
        });
        // View Renderer
        $this->set(ViewRenderer::class, function($c) {
            return new ViewRenderer($c->g(ViewManager::class));
        });
        // Pagination Paginator
        $this->set(Paginator::class, function($c) {
            return new Paginator($c->g(PaginationRenderer::class));
        });
        // Pagination Renderer
        $this->set(PaginationRenderer::class, function($c) {
            return new PaginationRenderer($c->g(ViewRenderer::class));
        });
    }

    /**
     * Get the dialog wrapper
     *
     * @return Dialog
     */
    public function getDialog(): Dialog
    {
        return $this->g(Dialog::class);
    }

    /**
     * Get the view manager
     *
     * @return ViewManager
     */
    public function getViewManager(): ViewManager
    {
        return $this->g(ViewManager::class);
    }

    /**
     * Get the view facade
     *
     * @return ViewRenderer
     */
    public function getViewRenderer(): ViewRenderer
    {
        return $this->g(ViewRenderer::class);
    }

    /**
     * Get the paginator
     *
     * @return Paginator
     */
    public function getPaginator(): Paginator
    {
        return $this->g(Paginator::class);
    }
}
