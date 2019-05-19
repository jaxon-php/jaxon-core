<?php

namespace Jaxon\App\Features;

use Jaxon\Contracts\Template\Renderer as TemplateRenderer;
use Jaxon\App\View\Facade as ViewFacade;

trait App
{

    /**
     * Wrap the Jaxon response into an HTTP response and send it back to the browser.
     *
     * @param  $code        The HTTP Response code
     *
     * @return HTTP Response
     */
    abstract public function httpResponse($code = '200');

    /**
     * Get the Jaxon response.
     *
     * @return Response
     */
    public function ajaxResponse()
    {
        return jaxon()->getResponse();
    }

    /**
     * Set the Jaxon application options.
     *
     * @param Config        $xAppConfig        The config options
     *
     * @return void
     */
    private function _setupApp($xAppConfig)
    {
        $di = jaxon()->di();
        // Register user functions and classes
        $di->getPluginManager()->registerFromConfig($xAppConfig);
        // Save the view namespaces
        $di->getViewManager()->addNamespaces($xAppConfig);
    }

    /**
     * Wraps the module/package/bundle setup method.
     *
     * @return void
     */
    private function _bootstrap(Options $xOptions)
    {
        $jaxon = jaxon();
        $di = $jaxon->di();
        $app = $jaxon->app();
        $view = $di->getViewManager();

        // Event before setting up the module
        $app->triggerEvent('pre.setup');

        // Add the view renderer
        $view->addRenderer('jaxon', function () {
            return new View\View();
        });

        // Set the pagination view namespace
        $view->addNamespace('pagination', '', '', 'jaxon');

        // Set the the view facade as template renderer
        $di->alias(TemplateRenderer::class, ViewFacade::class);
        // Setup the lib config options.
        $di->getConfig()->setOptions($xOptions->lib());

        // Event before the module has set the config
        $app->triggerEvent('pre.config');

        // Get the app config options.
        $xAppConfig = $di->newConfig($xOptions->app());
        // Setup the app.
        $this->_setupApp($xAppConfig);

        // Event after the module has read the config
        $app->triggerEvent('post.config');

        // Use the Composer autoloader. It's important to call this before triggers and callbacks.
        // $jaxon->useComposerAutoloader();
        // Jaxon library settings
        $xJs = $xOptions->js();
        if(!$jaxon->hasOption('js.app.extern'))
        {
            $jaxon->setOption('js.app.extern', $xJs->export());
        }
        if(!$jaxon->hasOption('js.app.minify'))
        {
            $jaxon->setOption('js.app.minify', $xJs->minify());
        }
        if(!$jaxon->hasOption('js.app.uri'))
        {
            $jaxon->setOption('js.app.uri', $xJs->uri());
        }
        if(!$jaxon->hasOption('js.app.dir'))
        {
            $jaxon->setOption('js.app.dir', $xJs->dir());
        }
        // Set the request URI
        if(!$jaxon->hasOption('core.request.uri'))
        {
            $jaxon->setOption('core.request.uri', 'jaxon');
        }

        // Event after setting up the module
        $app->triggerEvent('post.setup');
    }

    /**
     * Get the view renderer
     *
     * @return Jaxon\App\View\Facade
     */
    public function view()
    {
        return jaxon()->di()->getViewRenderer();
    }

    /**
     * Get the session manager
     *
     * @return Jaxon\App\Contracts\Session
     */
    public function session()
    {
        return jaxon()->di()->getSessionManager();
    }

    /**
     * Get the javascript code to be sent to the browser.
     *
     * @return string  the javascript code
     */
    public function script($bIncludeJs = false, $bIncludeCss = false)
    {
        return jaxon()->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function js()
    {
        return jaxon()->getJs();
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string  the javascript code
     */
    public function css()
    {
        return jaxon()->getCss();
    }
}
