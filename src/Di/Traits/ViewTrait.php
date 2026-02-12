<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Dialog\Library\AlertInterface;
use Jaxon\App\Dialog\Library\ConfirmInterface;
use Jaxon\App\Dialog\Library\ModalInterface;
use Jaxon\App\Dialog\Library\NoDialogLibrary;
use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\App\Dialog\Manager\LibraryRegistryInterface;
use Jaxon\App\Pagination\PaginationRenderer;
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
    private function registerViews(): void
    {
        // Jaxon template view
        $this->set(TemplateView::class, fn(Container $di) =>
            new TemplateView($di->g(TemplateEngine::class)));
        // View Renderer
        $this->set(ViewRenderer::class, function(Container $di) {
            $xViewRenderer = new ViewRenderer($di->g(Container::class));
            // Add the default view renderer
            $xViewRenderer->addRenderer('jaxon', fn(Container $di) => $di->g(TemplateView::class));
            $sTemplateDir = rtrim(trim($di->g('jaxon.core.dir.template')), '/\\');
            $sPaginationDir = $sTemplateDir . DIRECTORY_SEPARATOR . 'pagination';
            // By default, render pagination templates with Jaxon.
            $xViewRenderer->addNamespace('jaxon', $sTemplateDir, '.php', 'jaxon');
            $xViewRenderer->addNamespace('pagination', $sPaginationDir, '.php', 'jaxon');
            return $xViewRenderer;
        });

        // By default there is no dialog library registry.
        $this->set(NoDialogLibrary::class, fn() => new NoDialogLibrary());
        $this->set(LibraryRegistryInterface::class, fn(Container $di) =>
            new class($di) implements LibraryRegistryInterface
            {
                public function __construct(private $di)
                {}
                public function getAlertLibrary(): AlertInterface
                {
                    return $this->di->g(NoDialogLibrary::class);
                }
                public function getConfirmLibrary(): ConfirmInterface
                {
                    return $this->di->g(NoDialogLibrary::class);
                }
                public function getModalLibrary(): ?ModalInterface
                {
                    return null;
                }
            });
        // Dialog command
        $this->set(DialogCommand::class, fn(Container $di) =>
            new DialogCommand(fn() => $di->g(LibraryRegistryInterface::class)));

        // Pagination renderer
        $this->set(PaginationRenderer::class, fn(Container $di) =>
            new PaginationRenderer($di->g(ViewRenderer::class)));

        // Helpers for HTML custom attributes formatting
        $this->set(HtmlAttrHelper::class, fn(Container $di) =>
            new HtmlAttrHelper($di->g(ComponentContainer::class)));
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

    /**
     * Get the pagination renderer
     *
     * @return PaginationRenderer
     */
    public function getPaginationRenderer(): PaginationRenderer
    {
        return $this->g(PaginationRenderer::class);
    }
}
