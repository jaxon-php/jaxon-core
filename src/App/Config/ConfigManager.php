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
    protected $xConfig;

    /**
     * @var ConfigReader
     */
    protected $xConfigReader;

    /**
     * @var ConfigEventManager
     */
    protected $xEventManager;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * The constructor
     *
     * @param ConfigReader $xConfigReader
     * @param ConfigEventManager $xEventManager
     * @param Translator $xTranslator
     */
    public function __construct(ConfigReader $xConfigReader, ConfigEventManager $xEventManager, Translator $xTranslator)
    {
        $this->xConfigReader = $xConfigReader;
        $this->xEventManager = $xEventManager;
        $this->xTranslator = $xTranslator;
        $this->xConfig = new Config();
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
            $this->xConfig->setOptions($this->read($sConfigFile), $sConfigSection);
            // Call the config change listeners.
            $this->xEventManager->onChange($this->xConfig, '');
        }
        catch(DataDepth $e)
        {
            $sMessage = $this->xTranslator->trans('errors.data.depth',
                ['key' => $e->sPrefix, 'depth' => $e->nDepth]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Set the config options of the library
     *
     * @param array $aOptions
     * @param string $sKeys
     *
     * @return bool
     * @throws SetupException
     */
    public function setOptions(array $aOptions, string $sKeys = ''): bool
    {
        try
        {
            if(!$this->xConfig->setOptions($aOptions, $sKeys))
            {
                return false;
            }
            // Call the config change listeners.
            $this->xEventManager->onChange($this->xConfig, '');
            return true;
        }
        catch(DataDepth $e)
        {
            $sMessage = $this->xTranslator->trans('errors.data.depth',
                ['key' => $e->sPrefix, 'depth' => $e->nDepth]);
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
        $this->xConfig->setOption($sName, $xValue);
        // Call the config change listeners.
        $this->xEventManager->onChange($this->xConfig, $sName);
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
        return $this->xConfig->getOption($sName, $xDefault);
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
        return $this->xConfig->hasOption($sName);
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
        return $this->xConfig->getOptionNames($sPrefix);
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
            $sMessage = $this->xTranslator->trans('errors.data.depth',
                ['key' => $e->sPrefix, 'depth' => $e->nDepth]);
            throw new SetupException($sMessage);
        }
    }
}
