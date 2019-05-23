<?php

/**
 * Boot.php - Jaxon application bootstrapper
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App;

use Jaxon\Config\Config;

class Bootstrap
{
    /**
     * The library options
     *
     * @var array
     */
    private $aLibOptions = [];

    /**
     * The application options
     *
     * @var array
     */
    private $aAppOptions = [];

    /**
     * The Ajax endpoint URI
     *
     * @var string
     */
    private $sUri = '';

    /**
     * The js code URI
     *
     * @var string
     */
    private $sJsUri = '';

    /**
     * The js code dir
     *
     * @var string
     */
    private $sJsDir = '';

    /**
     * Export the js code
     *
     * @var boolean
     */
    private $bExportJs = false;

    /**
     * Minify the js code
     *
     * @var boolean
     */
    private $bMinifyJs = false;

    /**
     * Set the library options
     *
     * @var array       $aLibOptions    The library options
     *
     * @return Boot
     */
    public function lib(array $aLibOptions)
    {
        $this->aLibOptions = $aLibOptions;
        return $this;
    }

    /**
     * Set the applications options
     *
     * @var array       $aAppOptions    The application options
     *
     * @return Boot
     */
    public function app(array $aAppOptions)
    {
        $this->aAppOptions = $aAppOptions;
        return $this;
    }

    /**
     * Set the ajax endpoint URI
     *
     * @var string  $sUri   The ajax endpoint URI
     *
     * @return Boot
     */
    public function uri($sUri)
    {
        $this->sUri = $sUri;
        return $this;
    }

    /**
     * Set the javascript code
     *
     * @var
     *
     * @return Boot
     */
    public function js($bExportJs, $sJsUri = '', $sJsDir = '', $bMinifyJs = false)
    {
        $this->sJsUri = $sJsUri;
        $this->sJsDir = $sJsDir;
        $this->bExportJs = $bExportJs;
        $this->bMinifyJs = $bMinifyJs;
        return $this;
    }

    /**
     * Set the Jaxon application options.
     *
     * @param Config        $xAppConfig        The config options
     *
     * @return void
     */
    private function setupApp($xAppConfig)
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
    public function bootstrap()
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
        $di->getConfig()->setOptions($this->aLibOptions);

        // Event before the module has set the config
        $app->triggerEvent('pre.config');

        // Get the app config options.
        $xAppConfig = $di->newConfig($this->aAppOptions);
        $xAppConfig->setOption('options.views.default', 'default');
        // Setup the app.
        $this->setupApp($xAppConfig);

        // Event after the module has read the config
        $app->triggerEvent('post.config');

        // Use the Composer autoloader. It's important to call this before triggers and callbacks.
        // $jaxon->useComposerAutoloader();
        // Jaxon library settings
        if(!$jaxon->hasOption('js.app.extern'))
        {
            $jaxon->setOption('js.app.extern', $this->bExportJs);
        }
        if(!$jaxon->hasOption('js.app.minify'))
        {
            $jaxon->setOption('js.app.minify', $this->bMinifyJs);
        }
        if(!$jaxon->hasOption('js.app.uri') && $this->sJsUri != '')
        {
            $jaxon->setOption('js.app.uri', $this->sJsUri);
        }
        if(!$jaxon->hasOption('js.app.dir') && $this->sJsDir != '')
        {
            $jaxon->setOption('js.app.dir', $this->sJsDir);
        }
        // Set the request URI
        if(!$jaxon->hasOption('core.request.uri') && $this->sUri != '')
        {
            $jaxon->setOption('core.request.uri', $this->sUri);
        }

        // Event after setting up the module
        $app->triggerEvent('post.setup');
    }
}
