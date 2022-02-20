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

use Jaxon\Exception\Error as ErrorException;
use Jaxon\Jaxon;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Request\Handler\Handler as RequestHandler;
use Jaxon\Ui\View\Manager as ViewManager;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\Exception\Data as DataException;

class Bootstrap
{
    use \Jaxon\Features\Event;

    /**
     * @var Jaxon
     */
    private $jaxon;

    /**
     * @var PluginManager
     */
    private $xPluginManager;

    /**
     * @var ViewManager
     */
    private $xViewManager;

    /**
     * @var RequestHandler
     */
    private $xRequestHandler;

    /**
     * The class constructor
     *
     * @param Jaxon $jaxon
     * @param PluginManager $xPluginManager
     * @param ViewManager $xViewManager
     * @param RequestHandler $xRequestHandler
     */
    public function __construct(Jaxon $jaxon, PluginManager $xPluginManager,
        ViewManager $xViewManager, RequestHandler $xRequestHandler)
    {
        $this->jaxon = $jaxon;
        $this->xPluginManager = $xPluginManager;
        $this->xViewManager = $xViewManager;
        $this->xRequestHandler = $xRequestHandler;
    }

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
     * @param array       $aLibOptions    The library options
     *
     * @return Bootstrap
     */
    public function lib(array $aLibOptions)
    {
        $this->aLibOptions = $aLibOptions;
        return $this;
    }

    /**
     * Set the applications options
     *
     * @param array       $aAppOptions    The application options
     *
     * @return Bootstrap
     */
    public function app(array $aAppOptions)
    {
        $this->aAppOptions = $aAppOptions;
        return $this;
    }

    /**
     * Set the ajax endpoint URI
     *
     * @param string  $sUri   The ajax endpoint URI
     *
     * @return Bootstrap
     */
    public function uri($sUri)
    {
        $this->sUri = $sUri;
        return $this;
    }

    /**
     * Set the javascript code
     *
     * @param boolean   $bExportJs      Whether to export the js code in a file
     * @param string    $sJsUri         The URI to access the js file
     * @param string    $sJsDir         The directory where to create the js file
     * @param boolean   $bMinifyJs      Whether to minify the exported js file
     *
     * @return Bootstrap
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
     * @param Config $xAppConfig The config options
     *
     * @return void
     * @throws ErrorException
     */
    private function setupApp($xAppConfig)
    {
        // Register user functions and classes
        $this->xPluginManager->registerFromConfig($xAppConfig);
        // Save the view namespaces
        $this->xViewManager->addNamespaces($xAppConfig);
        // Call the on boot callbacks
        $this->xRequestHandler->onBoot();
    }

    /**
     * Wraps the module/package/bundle setup method.
     *
     * @return void
     * @throws ErrorException|DataException
     */
    public function run()
    {
        $di = $this->jaxon->di();

        // Event before setting up the module
        $this->triggerEvent('pre.setup');

        // Setup the lib config options.
        $di->getConfig()->setOptions($this->aLibOptions);

        // Event before the module has set the config
        $this->triggerEvent('pre.config');

        // Get the app config options.
        $xAppConfig = $di->newConfig($this->aAppOptions);
        $xAppConfig->setOption('options.views.default', 'default');
        // Setup the app.
        $this->setupApp($xAppConfig);

        // Event after the module has read the config
        $this->triggerEvent('post.config');

        // Jaxon library settings
        if(!$this->jaxon->hasOption('js.app.export'))
        {
            $this->jaxon->setOption('js.app.export', $this->bExportJs);
        }
        if(!$this->jaxon->hasOption('js.app.minify'))
        {
            $this->jaxon->setOption('js.app.minify', $this->bMinifyJs);
        }
        if(!$this->jaxon->hasOption('js.app.uri') && $this->sJsUri != '')
        {
            $this->jaxon->setOption('js.app.uri', $this->sJsUri);
        }
        if(!$this->jaxon->hasOption('js.app.dir') && $this->sJsDir != '')
        {
            $this->jaxon->setOption('js.app.dir', $this->sJsDir);
        }
        // Set the request URI
        if(!$this->jaxon->hasOption('core.request.uri') && $this->sUri != '')
        {
            $this->jaxon->setOption('core.request.uri', $this->sUri);
        }

        // Event after setting up the module
        $this->triggerEvent('post.setup');
    }
}
