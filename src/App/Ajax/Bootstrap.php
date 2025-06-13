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
     * @var bool
     */
    private bool $bBootstrapped = false;

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
        $aJsOptions = [
            'export' => $bExport,
            'minify' => $bMinify,
        ];
        if($sUri !== '')
        {
            $aJsOptions['uri'] = $sUri;
        }
        if($sDir !== '')
        {
            $aJsOptions['dir'] = $sDir;
        }
        $this->xConfigManager->setOptions($aJsOptions, 'js.app');
        return $this;
    }

    /**
     * Set the Jaxon application options.
     *
     * @return void
     * @throws SetupException
     */
    private function setupApp()
    {
        // Save the app config.
        $this->xConfigManager->setAppOptions($this->aAppOptions);
        // Register user functions and classes
        $this->xPackageManager->registerFromConfig();
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
        $this->onBoot();
    }

    /**
     * These callbacks are called once, after the library is initialized.
     *
     * @return void
     */
    public function onBoot()
    {
        if($this->bBootstrapped)
        {
            return;
        }

        $this->bBootstrapped = true;
        $aBootCallbacks = $this->xCallbackManager->popBootCallbacks();
        foreach($aBootCallbacks as $aBootCallback)
        {
            call_user_func($aBootCallback);
        }
    }
}
