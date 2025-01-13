<?php

/**
 * ConfigManager.php - Jaxon config reader
 *
 * Extends the config reader in the jaxon-utils package, and provides exception handlers.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Config;

use Jaxon\App\I18n\Translator;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\ConfigReader;
use Jaxon\Utils\Config\ConfigSetter;
use Jaxon\Utils\Config\Exception\DataDepth;
use Jaxon\Utils\Config\Exception\FileAccess;
use Jaxon\Utils\Config\Exception\FileContent;
use Jaxon\Utils\Config\Exception\FileExtension;
use Jaxon\Utils\Config\Exception\YamlExtension;

class ConfigManager
{
    /**
     * @var Config
     */
    protected $xLibConfig;

    /**
     * @var Config
     */
    protected $xAppConfig;

    /**
     * The constructor
     *
     * @param array $aDefaultOptions
     * @param Translator $xTranslator
     * @param ConfigReader $xConfigReader
     * @param ConfigSetter $xConfigSetter
     * @param ConfigEventManager $xEventManager
     */
    public function __construct(array $aDefaultOptions, private Translator $xTranslator,
        private ConfigReader $xConfigReader, private ConfigSetter $xConfigSetter,
        private ConfigEventManager $xEventManager)
    {
        $this->xLibConfig = $xConfigSetter->newConfig($aDefaultOptions);
        $this->xAppConfig = $xConfigSetter->newConfig();
    }

    /**
     * Read a config file
     *
     * @param string $sConfigFile
     *
     * @return array
     * @throws SetupException
     */
    public function read(string $sConfigFile): array
    {
        try
        {
            return $this->xConfigReader->read($sConfigFile);
        }
        catch(YamlExtension $e)
        {
            $sMessage = $this->xTranslator->trans('errors.yaml.install');
            throw new SetupException($sMessage);
        }
        catch(FileExtension $e)
        {
            $sMessage = $this->xTranslator->trans('errors.file.extension', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
        catch(FileAccess $e)
        {
            $sMessage = $this->xTranslator->trans('errors.file.access', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
        catch(FileContent $e)
        {
            $sMessage = $this->xTranslator->trans('errors.file.content', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Read options from a config file and set the library config
     *
     * @param string $sConfigFile The full path to the config file
     * @param string $sConfigSection The section of the config file to be loaded
     *
     * @return void
     * @throws SetupException
     */
    public function load(string $sConfigFile, string $sConfigSection = '')
    {
        try
        {
            // Read the options and save in the config.
            $this->xLibConfig = $this->xConfigSetter
                ->setOptions($this->xLibConfig, $this->read($sConfigFile), $sConfigSection);
            // Call the config change listeners.
            $this->xEventManager->libConfigChanged($this->xLibConfig, '');
        }
        catch(DataDepth $e)
        {
            $sMessage = $this->xTranslator->trans('errors.data.depth', [
                'key' => $e->sPrefix,
                'depth' => $e->nDepth,
            ]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Set the config options of the library
     *
     * @param array $aOptions
     * @param string $sNamePrefix A prefix for the config option names
     *
     * @return bool
     * @throws SetupException
     */
    public function setOptions(array $aOptions, string $sNamePrefix = ''): bool
    {
        try
        {
            $this->xLibConfig = $this->xConfigSetter
                ->setOptions($this->xLibConfig, $aOptions, $sNamePrefix);
            // Call the config change listeners.
            $this->xEventManager->libConfigChanged($this->xLibConfig, '');
            return $this->xLibConfig->changed();
        }
        catch(DataDepth $e)
        {
            $sMessage = $this->xTranslator->trans('errors.data.depth', [
                'key' => $e->sPrefix,
                'depth' => $e->nDepth,
            ]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Set the value of a config option
     *
     * @param string $sName The option name
     * @param mixed $xValue The option value
     *
     * @return void
     */
    public function setOption(string $sName, $xValue)
    {
        $this->xLibConfig = $this->xConfigSetter
            ->setOption($this->xLibConfig, $sName, $xValue);
        // Call the config change listeners.
        $this->xEventManager->libConfigChanged($this->xLibConfig, $sName);
    }

    /**
     * Get the value of a config option
     *
     * @param string $sName The option name
     * @param mixed $xDefault The default value, to be returned if the option is not defined
     *
     * @return mixed
     */
    public function getOption(string $sName, $xDefault = null)
    {
        return $this->xLibConfig->getOption($sName, $xDefault);
    }

    /**
     * Check the presence of a config option
     *
     * @param string $sName The option name
     *
     * @return bool
     */
    public function hasOption(string $sName): bool
    {
        return $this->xLibConfig->hasOption($sName);
    }

    /**
     * Get the names of the options matching a given prefix
     *
     * @param string $sPrefix The prefix to match
     *
     * @return array
     */
    public function getOptionNames(string $sPrefix): array
    {
        return $this->xLibConfig->getOptionNames($sPrefix);
    }

    /**
     * Set the value of a config option
     *
     * @param string $sName The option name
     * @param mixed $xValue The option value
     *
     * @return void
     */
    public function setAppOption(string $sName, $xValue)
    {
        $this->xAppConfig = $this->xConfigSetter
            ->setOption($this->xAppConfig, $sName, $xValue);
        // Call the config change listeners.
        $this->xEventManager->appConfigChanged($this->xAppConfig, $sName);
    }

    /**
     * Get the application config
     *
     * @return Config
     */
    public function getAppConfig(): Config
    {
        return $this->xAppConfig;
    }

    /**
     * Set the application config options
     *
     * @param array $aAppOptions
     *
     * @return void
     */
    public function setAppOptions(array $aAppOptions)
    {
        try
        {
            $this->xAppConfig = $this->xConfigSetter
                ->setOptions($this->xAppConfig, $aAppOptions);
            // Call the config change listeners.
            $this->xEventManager->appConfigChanged($this->xAppConfig, '');
        }
        catch(DataDepth $e)
        {
            $sMessage = $this->xTranslator->trans('errors.data.depth', [
                'key' => $e->sPrefix,
                'depth' => $e->nDepth,
            ]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Get the value of an application config option
     *
     * @param string $sName The option name
     * @param mixed $xDefault The default value, to be returned if the option is not defined
     *
     * @return mixed
     */
    public function getAppOption(string $sName, $xDefault = null)
    {
        return $this->xAppConfig->getOption($sName, $xDefault);
    }

    /**
     * Check the presence of an application config option
     *
     * @param string $sName The option name
     *
     * @return bool
     */
    public function hasAppOption(string $sName): bool
    {
        return $this->xAppConfig->hasOption($sName);
    }

    /**
     * Create a new the config object
     *
     * @param array $aOptions     The options array
     * @param string $sNamePrefix A prefix for the config option names
     *
     * @return Config
     * @throws SetupException
     */
    public function newConfig(array $aOptions = [], string $sNamePrefix = ''): Config
    {
        try
        {
            return $this->xConfigSetter->newConfig($aOptions, $sNamePrefix);
        }
        catch(DataDepth $e)
        {
            $sMessage = $this->xTranslator->trans('errors.data.depth', [
                'key' => $e->sPrefix,
                'depth' => $e->nDepth,
            ]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * @param string $sClassName
     *
     * @return void
     */
    public function addLibEventListener(string $sClassName)
    {
        $this->xEventManager->addLibConfigListener($sClassName);
    }

    /**
     * @param string $sClassName
     *
     * @return void
     */
    public function addAppEventListener(string $sClassName)
    {
        $this->xEventManager->addAppConfigListener($sClassName);
    }
}
