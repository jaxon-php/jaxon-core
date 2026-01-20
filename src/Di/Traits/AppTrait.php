<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Ajax\App;
use Jaxon\App\Ajax\AppInterface;
use Jaxon\App\Ajax\Bootstrap;
use Jaxon\App\Config\ConfigEventManager;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Config\ConfigReader;
use Jaxon\Config\ConfigSetter;
use Jaxon\Di\Container;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Plugin\Manager\PackageManager;

use function dirname;

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
    private function registerApp(): void
    {
        // Config Manager
        $this->set(ConfigEventManager::class, fn($di) => new ConfigEventManager($di->g(Container::class)));
        $this->set(ConfigManager::class, function($di) {
            $aDefaultOptions = require(dirname(__DIR__, 3) . '/config/lib.php');
            return new ConfigManager($aDefaultOptions, $di->g(Translator::class),
                $di->g(ConfigReader::class), $di->g(ConfigSetter::class),
                $di->g(ConfigEventManager::class));
        });

        // Jaxon App
        $this->set(AppInterface::class, fn() => new App());
        // Jaxon App bootstrap
        $this->set(Bootstrap::class, fn($di) => new Bootstrap($di->g(ConfigManager::class),
            $di->g(PackageManager::class), $di->g(CallbackManager::class)));
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
     * Get the config manager
     *
     * @return ConfigManager
     */
    public function config(): ConfigManager
    {
        return $this->g(ConfigManager::class);
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

    /**
     * Get the default request URI
     *
     * @return string
     */
    public function getRequestUri(): string
    {
        return $this->config()->getOption('core.request.uri',
            $this->getParameterReader()->uri());
    }
}
