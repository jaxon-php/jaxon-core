<?php

namespace Jaxon\Di\Traits;

use Jaxon\Di\Container;
use Jaxon\Ui\Dialog\Library\DialogLibraryManager;
use Jaxon\Ui\Pagination\PaginationRenderer;
use Jaxon\Ui\Pagination\Paginator;
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
        // Dialog library manager
        $this->set(DialogLibraryManager::class, function($c) {
            return new DialogLibraryManager($c->g(Container::class));
        });
    }

    /**
     * Get the dialog wrapper
     *
     * @return DialogFacade
     */
    public function getDialog(): DialogFacade
    {
        return $this->g(DialogFacade::class);
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
