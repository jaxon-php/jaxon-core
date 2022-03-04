<?php

namespace Jaxon\Container\Traits;

use Jaxon\Utils\Config\Config;
use Jaxon\Utils\File\Minifier;
use Jaxon\Utils\Http\URI;
use Jaxon\Utils\Template\Engine as TemplateEngine;
use Jaxon\Utils\Translation\Translator;

use function rtrim;
use function trim;

trait UtilTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerUtils()
    {
        // Translator
        $this->set(Translator::class, function($c) {
            $xTranslator = new Translator($c->g(Config::class)->getOption('language', ''));
            $sResourceDir = rtrim(trim($c->g('jaxon.core.dir.translation')), '/\\');
            // Load the Jaxon package translations
            $xTranslator->loadTranslations($sResourceDir . '/en/errors.php', 'en');
            $xTranslator->loadTranslations($sResourceDir . '/fr/errors.php', 'fr');
            $xTranslator->loadTranslations($sResourceDir . '/es/errors.php', 'es');
            // Load the config translations
            $xTranslator->loadTranslations($sResourceDir . '/en/config.php', 'en');
            $xTranslator->loadTranslations($sResourceDir . '/fr/config.php', 'fr');
            $xTranslator->loadTranslations($sResourceDir . '/es/config.php', 'es');
            // Load the upload translations
            $xTranslator->loadTranslations($sResourceDir . '/en/upload.php', 'en');
            $xTranslator->loadTranslations($sResourceDir . '/fr/upload.php', 'fr');
            $xTranslator->loadTranslations($sResourceDir . '/es/upload.php', 'es');
            return $xTranslator;
        });
        // Template engine
        $this->set(TemplateEngine::class, function($c) {
            $xTemplateEngine = new TemplateEngine();
            $sTemplateDir = rtrim(trim($c->g('jaxon.core.dir.template')), '/\\');
            $sPaginationDir = $sTemplateDir . DIRECTORY_SEPARATOR . 'pagination';
            $xTemplateEngine->addNamespace('jaxon', $sTemplateDir, '.php');
            $xTemplateEngine->addNamespace('pagination', $sPaginationDir, '.php');
            return $xTemplateEngine;
        });
        // Minifier
        $this->set(Minifier::class, function() {
            return new Minifier();
        });
        // URI decoder
        $this->set(URI::class, function() {
            return new URI();
        });
    }

    /**
     * Get the translator
     *
     * @return Translator
     */
    public function getTranslator(): Translator
    {
        return $this->g(Translator::class);
    }

    /**
     * Get the validator
     *
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return $this->g(Validator::class);
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
     * Get the minifier
     *
     * @return Minifier
     */
    public function getMinifier(): Minifier
    {
        return $this->g(Minifier::class);
    }
}
