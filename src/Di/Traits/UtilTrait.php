<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Stash\Stash;
use Jaxon\App\I18n\Translator;
use Jaxon\Config\Config;
use Jaxon\Config\ConfigReader;
use Jaxon\Config\ConfigSetter;
use Jaxon\Di\Container;
use Jaxon\Storage\StorageManager;
use Jaxon\Utils\Http\UriDetector;
use Jaxon\Utils\Template\TemplateEngine;
use Jaxon\Utils\Translation\Translator as BaseTranslator;

use function rtrim;
use function trim;

trait UtilTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerUtils(): void
    {
        // Translator
        $this->set(Translator::class, function(Container $di) {
            $xTranslator = new Translator();
            $sResourceDir = rtrim(trim($di->g('jaxon.core.dir.translation')), '/\\');
            // Load the debug translations
            $xTranslator->loadTranslations("$sResourceDir/en/errors.php", 'en');
            $xTranslator->loadTranslations("$sResourceDir/fr/errors.php", 'fr');
            $xTranslator->loadTranslations("$sResourceDir/es/errors.php", 'es');
            // Load the config translations
            $xTranslator->loadTranslations("$sResourceDir/en/config.php", 'en');
            $xTranslator->loadTranslations("$sResourceDir/fr/config.php", 'fr');
            $xTranslator->loadTranslations("$sResourceDir/es/config.php", 'es');
            // Load the labels translations
            $xTranslator->loadTranslations("$sResourceDir/en/labels.php", 'en');
            $xTranslator->loadTranslations("$sResourceDir/fr/labels.php", 'fr');
            $xTranslator->loadTranslations("$sResourceDir/es/labels.php", 'es');

            return $xTranslator;
        });
        // Define an alis for the translator with the base class name.
        $this->alias(BaseTranslator::class, Translator::class);

        // Config reader
        $this->set(ConfigReader::class, fn(Container $di): ConfigReader =>
            new ConfigReader($di->g(ConfigSetter::class)));

        // Config setter
        $this->set(ConfigSetter::class, fn() => new ConfigSetter());

        // Template engine
        $this->set(TemplateEngine::class, function(Container $di) {
            $xTemplateEngine = new TemplateEngine();
            $sTemplateDir = rtrim(trim($di->g('jaxon.core.dir.template')), '/\\');
            $sPaginationDir = $sTemplateDir . DIRECTORY_SEPARATOR . 'pagination';
            $xTemplateEngine->addNamespace('jaxon', $sTemplateDir, '.php');
            $xTemplateEngine->addNamespace('pagination', $sPaginationDir, '.php');
            $xTemplateEngine->setDefaultNamespace('jaxon');

            return $xTemplateEngine;
        });

        // URI detector
        $this->set(UriDetector::class, fn(): UriDetector => new UriDetector());

        // Temp cache for Jaxon components
        $this->set(Stash::class, fn(): Stash => new Stash());

        // File storage
        $this->set(StorageManager::class, function(Container $di): StorageManager {
            $xConfigGetter = function() use($di): Config {
                $aConfigOptions = $di->config()->getAppOption('storage', []);
                return $di->g(ConfigSetter::class)->newConfig($aConfigOptions);
            };

            return new StorageManager($xConfigGetter, $di->g(Translator::class));
        });
    }

    /**
     * Get the template engine
     *
     * @return TemplateEngine
     */
    public function getTemplateEngine(): TemplateEngine
    {
        return $this->g(TemplateEngine::class);
    }

    /**
     * Get the temp cache for Jaxon components
     *
     * @return Stash
     */
    public function getStash(): Stash
    {
        return $this->g(Stash::class);
    }
}
