<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Translator;
use Jaxon\Config\ConfigManager;
use Jaxon\Di\Container;
use Jaxon\Request\Call\Paginator;
use Jaxon\Ui\Dialog\Library\DialogLibraryHelper;
use Jaxon\Ui\Dialog\Library\DialogLibraryManager;
use Jaxon\Ui\View\PaginationRenderer;
use Jaxon\Ui\View\TemplateView;
use Jaxon\Ui\View\ViewRenderer;
use Jaxon\Utils\Template\TemplateEngine;

use function call_user_func;
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
        // View Renderer
        $this->set(ViewRenderer::class, function($c) {
            $xViewRenderer = new ViewRenderer($c->g(Container::class));
            // Add the default view renderer
            $xViewRenderer->addRenderer('jaxon', function($di) {
                return new TemplateView($di->g(TemplateEngine::class));
            });
            $sTemplateDir = rtrim(trim($c->g('jaxon.core.dir.template')), '/\\');
            $sPaginationDir = $sTemplateDir . DIRECTORY_SEPARATOR . 'pagination';
            // By default, render pagination templates with Jaxon.
            $xViewRenderer->addNamespace('jaxon', $sTemplateDir, '.php', 'jaxon');
            $xViewRenderer->addNamespace('pagination', $sPaginationDir, '.php', 'jaxon');
            return $xViewRenderer;
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
            return new DialogLibraryManager($c->g(Container::class), $c->g(ConfigManager::class), $c->g(Translator::class));
        });
    }

    /**
     * Register a javascript dialog library adapter.
     *
     * @param string $sClass
     *
     * @return void
     */
    public function registerDialogLibrary(string $sClass)
    {
        $this->set($sClass, function($c) use($sClass) {
            // Set the protected attributes of the library
            $cSetter = function() use($c) {
                $this->xHelper = new DialogLibraryHelper($this, $c->g(ConfigManager::class), $c->g(TemplateEngine::class));
            };
            // Can now access protected attributes
            $xLibrary = $c->make($sClass);
            call_user_func($cSetter->bindTo($xLibrary, $xLibrary));
            return $xLibrary;
        });
    }

    /**
     * Get the dialog library manager
     *
     * @return DialogLibraryManager
     */
    public function getDialogLibraryManager(): DialogLibraryManager
    {
        return $this->g(DialogLibraryManager::class);
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
     * Get the paginator
     *
     * @return Paginator
     */
    public function getPaginator(): Paginator
    {
        return $this->g(Paginator::class);
    }
}
