<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Stash\Stash;
use Jaxon\App\I18n\Translator;
use Jaxon\Config\ConfigReader;
use Jaxon\Config\ConfigSetter;
use Jaxon\Utils\Http\UriDetector;
use Jaxon\Utils\Template\TemplateEngine;

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
        $this->set(Translator::class, function($di) {
            $xTranslator = new Translator();
            $sResourceDir = rtrim(trim($di->g('jaxon.core.dir.translation')), '/\\');
            // Load the debug translations
            $xTranslator->loadTranslations($sResourceDir . '/en/errors.php', 'en');
            $xTranslator->loadTranslations($sResourceDir . '/fr/errors.php', 'fr');
            $xTranslator->loadTranslations($sResourceDir . '/es/errors.php', 'es');
            // Load the config translations
            $xTranslator->loadTranslations($sResourceDir . '/en/config.php', 'en');
            $xTranslator->loadTranslations($sResourceDir . '/fr/config.php', 'fr');
            $xTranslator->loadTranslations($sResourceDir . '/es/config.php', 'es');
            // Load the labels translations
            $xTranslator->loadTranslations($sResourceDir . '/en/labels.php', 'en');
            $xTranslator->loadTranslations($sResourceDir . '/fr/labels.php', 'fr');
            $xTranslator->loadTranslations($sResourceDir . '/es/labels.php', 'es');
            return $xTranslator;
        });

        // Config reader
        $this->set(ConfigReader::class, function($di) {
            return new ConfigReader($di->g(ConfigSetter::class));
        });
        // Config setter
        $this->set(ConfigSetter::class, function() {
            return new ConfigSetter();
        });
        // Template engine
        $this->set(TemplateEngine::class, function($di) {
            $xTemplateEngine = new TemplateEngine();
            $sTemplateDir = rtrim(trim($di->g('jaxon.core.dir.template')), '/\\');
            $sPaginationDir = $sTemplateDir . DIRECTORY_SEPARATOR . 'pagination';
            $xTemplateEngine->addNamespace('jaxon', $sTemplateDir, '.php');
            $xTemplateEngine->addNamespace('pagination', $sPaginationDir, '.php');
            $xTemplateEngine->setDefaultNamespace('jaxon');
            return $xTemplateEngine;
        });
        // URI detector
        $this->set(UriDetector::class, function() {
            return new UriDetector();
        });

        // Temp cache for Jaxon components
        $this->set(Stash::class, function() {
            return new Stash();
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
