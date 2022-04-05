<?php

namespace Jaxon\Di\Traits;

use Jaxon\Di\Container;
use Jaxon\Ui\Dialogs\DialogFacade;
use Jaxon\Ui\Pagination\Paginator;
use Jaxon\Ui\Pagination\PaginationRenderer;
use Jaxon\Ui\Template\TemplateView;
use Jaxon\Ui\View\ViewManager;
use Jaxon\Ui\View\ViewRenderer;
use Jaxon\Utils\Template\TemplateEngine;

trait ViewTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerViews()
    {
        // Dialog Facade
        $this->set(DialogFacade::class, function($c) {
            return new DialogFacade($c->g(Container::class));
        });
        // View Manager
        $this->set(ViewManager::class, function($c) {
            $xViewManager = new ViewManager($this);
            // Add the default view renderer
            $xViewManager->addRenderer('jaxon', function($di) {
                return new TemplateView($di->g(TemplateEngine::class));
            });
            $sTemplateDir = rtrim(trim($c->g('jaxon.core.dir.template')), '/\\');
            $sPaginationDir = $sTemplateDir . DIRECTORY_SEPARATOR . 'pagination';
            // By default, render pagination templates with Jaxon.
            $xViewManager->addNamespace('jaxon', $sTemplateDir, '.php', 'jaxon');
            $xViewManager->addNamespace('pagination', $sPaginationDir, '.php', 'jaxon');
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
