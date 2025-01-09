<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Ajax\App;
use Jaxon\App\Ajax\AppInterface;
use Jaxon\App\Ajax\Bootstrap;
use Jaxon\App\Config\ConfigEventManager;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Di\Container;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Plugin\Manager\PackageManager;
use Jaxon\Utils\Config\ConfigReader;

trait AppTrait
{
    /**
     * @var string
     */
    private $sJsLibVersion = 'jaxon_javascript_library_version';

    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerApp()
    {
        // Config Manager
        $this->set(ConfigEventManager::class, function($di) {
            return new ConfigEventManager($di->g(Container::class));
        });
        $this->set(ConfigManager::class, function($di) {
            $aDefaultOptions = require(__DIR__ . '/../../../config/lib.php');
            return new ConfigManager($aDefaultOptions, $di->g(ConfigReader::class),
                $di->g(ConfigEventManager::class), $di->g(Translator::class));
        });

        // Jaxon App
        $this->set(AppInterface::class, function() {
            return new App();
        });
        // Jaxon App bootstrap
        $this->set(Bootstrap::class, function($di) {
            return new Bootstrap($di->g(ConfigManager::class), $di->g(PackageManager::class),
                $di->g(CallbackManager::class), $di->g(ViewRenderer::class));
        });
        // The javascript library version
        $this->set($this->sJsLibVersion, function($di) {
            $xRequest = $di->getRequest();
            $aParams = $xRequest->getMethod() === 'POST' ?
                $xRequest->getParsedBody() : $xRequest->getQueryParams();
            return $aParams['jxnv'] ?? '3.3.0';
        });
    }

    /**
     * Register the event handlers
     *
     * @return void
     */
    private function setEventHandlers(): void
    {
        /** @var ConfigEventManager */
        $xEventManager = $this->g(ConfigEventManager::class);
        $xEventManager->addLibConfigListener(Translator::class);
    }

    /**
     * Get the App instance
     *
     * @return AppInterface
     */
    public function getApp(): AppInterface
    {
        return $this->g(AppInterface::class);
    }

    /**
     * Get the App bootstrap
     *
     * @return Bootstrap
     */
    public function getBootstrap(): Bootstrap
    {
        return $this->g(Bootstrap::class);
    }

    /**
     * Get the javascript library version
     *
     * @return string
     */
    public function getJsLibVersion(): string
    {
        return $this->g($this->sJsLibVersion);
    }
}
