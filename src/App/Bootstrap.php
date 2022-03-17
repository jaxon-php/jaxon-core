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

use Jaxon\Jaxon;
use Jaxon\Config\ConfigManager;
use Jaxon\Plugin\PluginManager;
use Jaxon\Request\Handler\RequestHandler;
use Jaxon\Ui\View\ViewManager;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Config\Exception\DataDepth;

class Bootstrap
{
    /**
     * @var ConfigManager
     */
    private $xConfigManager;

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
     * @var Translator
     */
    private $xTranslator;

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
     * @var bool
     */
    private $bExportJs = false;

    /**
     * Minify the js code
     *
     * @var bool
     */
    private $bMinifyJs = false;

    /**
     * The class constructor
     *
     * @param ConfigManager $xConfigManager
     * @param PluginManager $xPluginManager
     * @param ViewManager $xViewManager
     * @param RequestHandler $xRequestHandler
     * @param Translator $xTranslator
     */
    public function __construct(ConfigManager $xConfigManager, PluginManager $xPluginManager,
        ViewManager $xViewManager, RequestHandler $xRequestHandler, Translator $xTranslator)
    {
        $this->xConfigManager = $xConfigManager;
        $this->xPluginManager = $xPluginManager;
        $this->xViewManager = $xViewManager;
        $this->xRequestHandler = $xRequestHandler;
        $this->xTranslator = $xTranslator;
    }

    /**
     * Set the library options
     *
     * @param array $aLibOptions    The library options
     *
     * @return Bootstrap
     */
    public function lib(array $aLibOptions): Bootstrap
    {
        $this->aLibOptions = $aLibOptions;
        return $this;
    }

    /**
     * Set the applications options
     *
     * @param array $aAppOptions    The application options
     *
     * @return Bootstrap
     */
    public function app(array $aAppOptions): Bootstrap
    {
        $this->aAppOptions = $aAppOptions;
        return $this;
    }

    /**
     * Set the ajax endpoint URI
     *
     * @param string $sUri    The ajax endpoint URI
     *
     * @return Bootstrap
     */
    public function uri(string $sUri): Bootstrap
    {
        $this->sUri = $sUri;
        return $this;
    }

    /**
     * Set the javascript code
     *
     * @param bool $bExportJs    Whether to export the js code in a file
     * @param string $sJsUri    The URI to access the js file
     * @param string $sJsDir    The directory where to create the js file
     * @param bool $bMinifyJs    Whether to minify the exported js file
     *
     * @return Bootstrap
     */
    public function js(bool $bExportJs, string $sJsUri = '', string $sJsDir = '', bool $bMinifyJs = false): Bootstrap
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
     * @param Config $xAppConfig    The config options
     *
     * @return void
     * @throws SetupException
     */
    private function setupApp(Config $xAppConfig)
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
     * @throws SetupException
     */
    public function setup()
    {
        // Setup the lib config options.
        $this->xConfigManager->setOptions($this->aLibOptions);

        // Get the app config options.
        $xAppConfig = $this->xConfigManager->newConfig($this->aAppOptions);
        $xAppConfig->setOption('options.views.default', 'default');
        // Setup the app.
        $this->setupApp($xAppConfig);

        $xLibConfig = $this->xConfigManager->getConfig();
        // Jaxon library settings
        if(!$xLibConfig->hasOption('js.app.export'))
        {
            $xLibConfig->setOption('js.app.export', $this->bExportJs);
        }
        if(!$xLibConfig->hasOption('js.app.minify'))
        {
            $xLibConfig->setOption('js.app.minify', $this->bMinifyJs);
        }
        if(!$xLibConfig->hasOption('js.app.uri') && $this->sJsUri != '')
        {
            $xLibConfig->setOption('js.app.uri', $this->sJsUri);
        }
        if(!$xLibConfig->hasOption('js.app.dir') && $this->sJsDir != '')
        {
            $xLibConfig->setOption('js.app.dir', $this->sJsDir);
        }
        // Set the request URI
        if(!$xLibConfig->hasOption('core.request.uri') && $this->sUri != '')
        {
            $xLibConfig->setOption('core.request.uri', $this->sUri);
        }
    }
}
