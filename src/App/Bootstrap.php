<?php

/**
 * Bootstrap.php
 *
 * Jaxon application bootstrapper
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Manager\PackageManager;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Utils\Config\Config;

use function call_user_func;

class Bootstrap
{
    /**
     * @var ConfigManager
     */
    private $xConfigManager;

    /**
     * @var PackageManager
     */
    private $xPackageManager;

    /**
     * @var CallbackManager
     */
    private $xCallbackManager;

    /**
     * @var ViewRenderer
     */
    private $xViewRenderer;

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
     * The class constructor
     *
     * @param ConfigManager $xConfigManager
     * @param PackageManager $xPackageManager
     * @param CallbackManager $xCallbackManager
     * @param ViewRenderer $xViewRenderer
     */
    public function __construct(ConfigManager $xConfigManager, PackageManager $xPackageManager,
        CallbackManager $xCallbackManager, ViewRenderer $xViewRenderer)
    {
        $this->xConfigManager = $xConfigManager;
        $this->xPackageManager = $xPackageManager;
        $this->xCallbackManager = $xCallbackManager;
        $this->xViewRenderer = $xViewRenderer;
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
     * Set the javascript asset
     *
     * @param bool $bExport    Whether to export the js code in a file
     * @param bool $bMinify    Whether to minify the exported js file
     * @param string $sUri    The URI to access the js file
     * @param string $sDir    The directory where to create the js file
     *
     * @return Bootstrap
     */
    public function asset(bool $bExport, bool $bMinify, string $sUri = '', string $sDir = ''): Bootstrap
    {
        // Jaxon library settings
        $this->xConfigManager->setOption('js.app.export', $bExport);
        $this->xConfigManager->setOption('js.app.minify', $bMinify);
        if($sUri !== '')
        {
            $this->xConfigManager->setOption('js.app.uri', $sUri);
        }
        if($sDir !== '')
        {
            $this->xConfigManager->setOption('js.app.dir', $sDir);
        }
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
        $this->xPackageManager->registerFromConfig($xAppConfig);
    }

    /**
     * Wraps the module/package/bundle setup method.
     *
     * @return void
     * @throws SetupException
     */
    public function setup()
    {
        // Prevent the Jaxon library from sending the response or exiting
        $this->xConfigManager->setOption('core.response.send', false);
        $this->xConfigManager->setOption('core.process.exit', false);

        // Setup the lib config options.
        $this->xConfigManager->setOptions($this->aLibOptions);
        // Get the app config options.
        $xAppConfig = $this->xConfigManager->newConfig($this->aAppOptions);

        // Setup the app.
        $this->setupApp($xAppConfig);
        $this->onBoot();
    }

    /**
     * These callbacks are called right after the library is initialized.
     *
     * @return void
     */
    public function onBoot()
    {
        // Only call the callbacks that aren't called yet.
        $aBootCallbacks = $this->xCallbackManager->popBootCallbacks();
        foreach($aBootCallbacks as $aBootCallback)
        {
            call_user_func($aBootCallback);
        }
    }
}
