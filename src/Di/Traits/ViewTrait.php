<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\App\Dialog\Manager\LibraryRegistryInterface;
use Jaxon\App\Pagination\Renderer;
use Jaxon\App\Pagination\RendererInterface;
use Jaxon\App\View\Helper\HtmlAttrHelper;
use Jaxon\App\View\TemplateView;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Di\ComponentContainer;
use Jaxon\Di\Container;
use Jaxon\Utils\Template\TemplateEngine;

use function rtrim;
use function trim;

trait ViewTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerViews()
    {
        // Jaxon template view
        $this->set(TemplateView::class, function($di) {
            return new TemplateView($di->g(TemplateEngine::class));
        });
        // View Renderer
        $this->set(ViewRenderer::class, function($di) {
            $xViewRenderer = new ViewRenderer($di->g(Container::class));
            // Add the default view renderer
            $xViewRenderer->addRenderer('jaxon', function($di) {
                return $di->g(TemplateView::class);
            });
            $sTemplateDir = rtrim(trim($di->g('jaxon.core.dir.template')), '/\\');
            $sPaginationDir = $sTemplateDir . DIRECTORY_SEPARATOR . 'pagination';
            // By default, render pagination templates with Jaxon.
            $xViewRenderer->addNamespace('jaxon', $sTemplateDir, '.php', 'jaxon');
            $xViewRenderer->addNamespace('pagination', $sPaginationDir, '.php', 'jaxon');
            return $xViewRenderer;
        });

        // By default there is no dialog library registry.
        $this->set(LibraryRegistryInterface::class, function($di) {
            return null;
        });
        // Dialog command
        $this->set(DialogCommand::class, function($di) {
            return new DialogCommand($di->g(LibraryRegistryInterface::class));
        });
        // Pagination renderer
        $this->set(RendererInterface::class, function($di) {
            return new Renderer($di->g(ViewRenderer::class));
        });

        // Helpers for HTML custom attributes formatting
        $this->set(HtmlAttrHelper::class, function($di) {
            return new HtmlAttrHelper($di->g(ComponentContainer::class));
        });
    }

    /**
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    public function getViewRenderer(): ViewRenderer
    {
        return $this->g(ViewRenderer::class);
    }

    /**
     * Get the custom attributes helper
     *
     * @return HtmlAttrHelper
     */
    public function getHtmlAttrHelper(): HtmlAttrHelper
    {
        return $this->g(HtmlAttrHelper::class);
    }

    /**
     * Get the dialog command
     *
     * @return DialogCommand
     */
    public function getDialogCommand(): DialogCommand
    {
        return $this->g(DialogCommand::class);
    }
}
