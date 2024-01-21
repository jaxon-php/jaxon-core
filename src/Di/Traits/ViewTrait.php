<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\Library\AlertLibrary;
use Jaxon\App\Dialog\Library\DialogLibraryHelper;
use Jaxon\App\Dialog\Library\DialogLibraryManager;
use Jaxon\App\Dialog\LibraryInterface;
use Jaxon\App\Dialog\MessageInterface;
use Jaxon\App\Dialog\ModalInterface;
use Jaxon\App\Dialog\QuestionInterface;
use Jaxon\App\I18n\Translator;
use Jaxon\App\View\PaginationRenderer;
use Jaxon\App\View\TemplateView;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Di\Container;
use Jaxon\Request\Call\Paginator;
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

        // Pagination Paginator
        $this->set(Paginator::class, function($di) {
            return new Paginator($di->g(PaginationRenderer::class));
        });
        // Pagination Renderer
        $this->set(PaginationRenderer::class, function($di) {
            return new PaginationRenderer($di->g(ViewRenderer::class));
        });

        // Dialog library manager
        $this->set(DialogLibraryManager::class, function($di) {
            return new DialogLibraryManager($di->g(Container::class), $di->g(ConfigManager::class), $di->g(Translator::class));
        });
        $this->val(AlertLibrary::class, new AlertLibrary());
    }

    /**
     * Register a javascript dialog library adapter.
     *
     * @param string $sClass
     * @param string $sLibraryName
     *
     * @return void
     */
    public function registerDialogLibrary(string $sClass, string $sLibraryName)
    {
        $this->set($sClass, function($di) use($sClass) {
            // Set the protected attributes of the library
            $cSetter = function() use($di) {
                $this->xHelper = new DialogLibraryHelper($this, $di->g(ConfigManager::class), $di->g(TemplateEngine::class));
            };
            // Can now access protected attributes
            $xLibrary = $di->make($sClass);
            call_user_func($cSetter->bindTo($xLibrary, $xLibrary));
            return $xLibrary;
        });
        // Set the alias, so the libraries can be found by their names.
        $this->alias("dialog_library_$sLibraryName", $sClass);
    }

    /**
     * Get a dialog library
     *
     * @param string $sLibraryName
     *
     * @return LibraryInterface
     */
    public function getDialogLibrary(string $sLibraryName): LibraryInterface
    {
        return $this->g("dialog_library_$sLibraryName");
    }

    /**
     * Get the QuestionInterface library
     *
     * @param string $sLibraryName
     *
     * @return QuestionInterface
     */
    public function getQuestionLibrary(string $sLibraryName): QuestionInterface
    {
        $sKey = "dialog_library_$sLibraryName";
        return $this->h($sKey) ? $this->g($sKey) : $this->g(AlertLibrary::class);
    }

    /**
     * Get the MessageInterface library
     *
     * @param string $sLibraryName
     *
     * @return MessageInterface
     */
    public function getMessageLibrary(string $sLibraryName): MessageInterface
    {
        $sKey = "dialog_library_$sLibraryName";
        return $this->h($sKey) ? $this->g($sKey) : $this->g(AlertLibrary::class);
    }

    /**
     * Get the ModalInterface library
     *
     * @param string $sLibraryName
     *
     * @return ModalInterface|null
     */
    public function getModalLibrary(string $sLibraryName): ?ModalInterface
    {
        $sKey = "dialog_library_$sLibraryName";
        return $this->h($sKey) ? $this->g($sKey) : null;
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
