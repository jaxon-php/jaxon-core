<?php

namespace Jaxon\Container\Traits;

use Jaxon\Config\ConfigManager;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\Exception\DataDepth;
use Jaxon\Utils\Translation\Translator;

trait ConfigTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerConfigs()
    {
        $this->set(ConfigManager::class, function($c) {
            return new ConfigManager($c->g(Config::class), $c->g(Translator::class));
        });
        $this->set(Config::class, function($c) {
            return new Config($c->g('jaxon.core.options'));
        });
    }

    /**
     * Get the library config options
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->g(Config::class);
    }

    /**
     * Get the config reader
     *
     * @return ConfigManager
     */
    public function getConfigManager(): ConfigManager
    {
        return $this->g(ConfigManager::class);
    }

    /**
     * Create a new the config object
     *
     * @param array $aOptions    The options array
     * @param string $sKeys    The prefix of key of the config options
     *
     * @return Config
     * @throws SetupException
     */
    public function newConfig(array $aOptions = [], string $sKeys = ''): Config
    {
        try
        {
            return new Config($aOptions, $sKeys);
        }
        catch(DataDepth $e)
        {
            $xTranslator = $this->g(Translator::class);
            $sMessage = $xTranslator->trans('errors.data.depth', ['key' => $e->sPrefix, 'depth' => $e->nDepth]);
            throw new SetupException($sMessage);
        }
    }
}
