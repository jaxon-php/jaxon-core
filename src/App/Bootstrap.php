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

use Jaxon\Config\ConfigManager;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Utils\Config\Config;
use Jaxon\Exception\SetupException;

use function call_user_func;
use function count;

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
     * @var CallbackManager
     */
    private $xCallbackManager;

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
     * @param CallbackManager $xCallbackManager
     */
    public function __construct(ConfigManager $xConfigManager, PluginManager $xPluginManager, CallbackManager $xCallbackManager)
    {
        $this->xConfigManager = $xConfigManager;
        $this->xPluginManager = $xPluginManager;
        $this->xCallbackManager = $xCallbackManager;
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

        // Jaxon library settings
        if(!$this->xConfigManager->hasOption('js.app.export'))
        {
            $this->xConfigManager->setOption('js.app.export', $this->bExportJs);
        }
        if(!$this->xConfigManager->hasOption('js.app.minify'))
        {
            $this->xConfigManager->setOption('js.app.minify', $this->bMinifyJs);
        }
        if(!$this->xConfigManager->hasOption('js.app.uri') && $this->sJsUri != '')
        {
            $this->xConfigManager->setOption('js.app.uri', $this->sJsUri);
        }
        if(!$this->xConfigManager->hasOption('js.app.dir') && $this->sJsDir != '')
        {
            $this->xConfigManager->setOption('js.app.dir', $this->sJsDir);
        }
        // Set the request URI
        if(!$this->xConfigManager->hasOption('core.request.uri') && $this->sUri != '')
        {
            $this->xConfigManager->setOption('core.request.uri', $this->sUri);
        }
        $this->onBoot();
    }

    /**
     * These callbacks are called right after the library is initialized.
     *
     * @return void
     */
    public function onBoot()
    {
        if(!$this->xCallbackManager->bootCallbackAdded())
        {
            return;
        }
        // Only call the callbacks that aren't called yet.
        $aBootCallbacks = $this->xCallbackManager->getBootCallbacks();
        $nBootCallCount = $this->xCallbackManager->getBootCallCount();
        // Update the on boot calls
        $this->xCallbackManager->updateBootCalls();
        // Call the callbacks.
        $nBootCallbackTotal = count($aBootCallbacks);
        for($n = $nBootCallCount; $n < $nBootCallbackTotal; $n++)
        {
            call_user_func($aBootCallbacks[$n]);
        }
    }
}
