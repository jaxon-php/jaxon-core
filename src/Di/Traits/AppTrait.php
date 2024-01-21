<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\App;
use Jaxon\App\AppInterface;
use Jaxon\App\Bootstrap;
use Jaxon\App\Config\ConfigEventManager;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\Library\DialogLibraryManager;
use Jaxon\App\I18n\Translator;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Di\Container;
use Jaxon\Jaxon;
use Jaxon\Plugin\Manager\PackageManager;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Utils\Config\ConfigReader;

trait AppTrait
{
    /**
     * @var string
     */
    private $sJsLibVersion = 'jaxon_javascript_library_version';

    /**
     * The default config options
     *
     * @var array
     */
    protected $aConfig =  [
        'core' => [
            'version'               => Jaxon::VERSION,
            'language'              => 'en',
            'encoding'              => 'utf-8',
            'decode_utf8'           => false,
            'prefix' => [
                'function'          => 'jaxon_',
                'class'             => 'Jaxon',
            ],
            'request' => [
                // 'uri'            => '',
                'mode'              => 'asynchronous',
                'method'            => 'POST', // W3C: Method is case-sensitive
            ],
            'response' => [
                'send'              => true,
            ],
            'debug' => [
                'on'                => false,
                'verbose'           => false,
            ],
            'process' => [
                'exit'              => true,
                'timeout'           => 6000,
            ],
            'error' => [
                'handle'            => false,
                'log_file'          => '',
            ],
            'jquery' => [
                'no_conflict'       => false,
            ],
        ],
        'js' => [
            'lib' => [
                'output_id'         => 0,
                'queue_size'        => 0,
                'load_timeout'      => 2000,
                'show_status'       => false,
                'show_cursor'       => true,
            ],
            'app' => [
                'dir'               => '',
                'options'           => '',
            ],
        ],
    ];

    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerApp()
    {
        // Translator
        $this->set(Translator::class, function($di) {
            $xTranslator = new Translator();
            $sResourceDir = rtrim(trim($di->g('jaxon.core.dir.translation')), '/\\');
            // Load the Jaxon package translations
            $xTranslator->loadTranslations($sResourceDir . '/en/errors.php', 'en');
            $xTranslator->loadTranslations($sResourceDir . '/fr/errors.php', 'fr');
            $xTranslator->loadTranslations($sResourceDir . '/es/errors.php', 'es');
            // Load the config translations
            $xTranslator->loadTranslations($sResourceDir . '/en/config.php', 'en');
            $xTranslator->loadTranslations($sResourceDir . '/fr/config.php', 'fr');
            $xTranslator->loadTranslations($sResourceDir . '/es/config.php', 'es');
            return $xTranslator;
        });

        // Config Manager
        $this->set(ConfigEventManager::class, function($di) {
            return new ConfigEventManager($di->g(Container::class));
        });
        $this->set(ConfigManager::class, function($di) {
            $xEventManager = $di->g(ConfigEventManager::class);
            $xConfigManager = new ConfigManager($di->g(ConfigReader::class), $xEventManager, $di->g(Translator::class));
            $xConfigManager->setOptions($this->aConfig);
            // It's important to call this after the $xConfigManager->setOptions(),
            // because we don't want to trigger the events since the listeners cannot yet be instantiated.
            $xEventManager->addListener(Translator::class);
            $xEventManager->addListener(DialogLibraryManager::class);
            return $xConfigManager;
        });

        // Jaxon App
        $this->set(AppInterface::class, function($di) {
            return new App($di->g(Container::class));
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
