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

namespace Jaxon\App\Ajax;

use Jaxon\App\Config\ConfigManager;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Manager\PackageManager;
use Jaxon\Request\Handler\CallbackManager;

use function call_user_func;

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
     * The class constructor
     *
     * @param ConfigManager $xConfigManager
     * @param PackageManager $xPackageManager
     * @param CallbackManager $xCallbackManager
     */
    public function __construct(private ConfigManager $xConfigManager,
        private PackageManager $xPackageManager, private CallbackManager $xCallbackManager)
    {}

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
     * Set the javascript or css asset
     *
     * @param bool $bExport    Whether to export the code in a file
     * @param bool $bMinify    Whether to minify the exported file
     * @param string $sUri     The URI to access the exported file
     * @param string $sDir     The directory where to create the file
     * @param string $sType    The asset type: "js" or "css"
     *
     * @return Bootstrap
     */
    public function asset(bool $bExport, bool $bMinify,
        string $sUri = '', string $sDir = '', string $sType = ''): Bootstrap
    {
        // Don't change the existing assets config.
        if(!$this->xConfigManager->hasAppOption('assets'))
        {
            $this->xConfigManager->asset($bExport, $bMinify, $sUri, $sDir, $sType);
        }

        return $this;
    }

    /**
     * Set the Jaxon application options.
     *
     * @return void
     * @throws SetupException
     */
    private function setupApp(): void
    {
        // Save the app config.
        $this->xConfigManager->setAppOptions($this->aAppOptions);
        // Setup the DI container from the app config.
        $this->xPackageManager->updateContainer($this->xConfigManager->getAppConfig());
        // Register user functions and classes
        $this->xPackageManager->registerFromConfig();
    }

    /**
     * Wraps the module/package/bundle setup method.
     *
     * @return void
     * @throws SetupException
     */
    public function setup(): void
    {
        // Prevent the Jaxon library from sending the response or exiting
        $this->xConfigManager->setOptions([
            'response' => [
                'send' => false,
            ],
            'process' => [
                'exit' => false,
            ],
        ], 'core');
        // Setup the lib config options.
        $this->xConfigManager->setOptions($this->aLibOptions);

        // Setup the app.
        $this->setupApp();
    }

    /**
     * These callbacks are called once, after the library is initialized.
     *
     * @return void
     */
    public function onBoot(): void
    {
        // Popping the callbacks makes each of them to be called once.
        $aBootCallbacks = $this->xCallbackManager->popBootCallbacks();
        foreach($aBootCallbacks as $aBootCallback)
        {
            call_user_func($aBootCallback);
        }
    }
}
