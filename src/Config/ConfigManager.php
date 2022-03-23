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

namespace Jaxon\Config;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\ConfigReader;
use Jaxon\Utils\Translation\Translator;
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
     * @var Translator
     */
    protected $xTranslator;

    /**
     * @var array The default config options
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
                'method'            => 'POST', // W3C: Method is case sensitive
            ],
            'response' => [
                'send'              => true,
                'merge.ap'          => true,
                'merge.js'          => true,
            ],
            'debug' => [
                'on'                => false,
                'verbose'           => false,
            ],
            'process' => [
                'exit'              => true,
                'clean'             => false,
                'timeout'           => 6000,
            ],
            'error' => [
                'handle'            => false,
                'log_file'          => '',
            ],
            'jquery' => [
                'no_conflict'       => false,
            ],
            'upload' => [
                'enabled'           => true,
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
                'minify'            => true,
                'options'           => '',
            ],
        ],
    ];

    /**
     * The constructor
     *
     * @param ConfigReader $xConfigReader
     * @param Translator $xTranslator
     */
    public function __construct(ConfigReader $xConfigReader, Translator $xTranslator)
    {
        $this->xConfigReader = $xConfigReader;
        $this->xTranslator = $xTranslator;
        try
        {
            $this->xConfig = new Config($this->aConfig);
        }
        catch(DataDepth $e){} // This exception cannot actually be raised.
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
            $this->xConfigReader->load($this->xConfig, $sConfigFile, $sConfigSection);
            // Set the library language any time the config is changed.
            $this->xTranslator->setLocale($this->xConfig->getOption('core.language'));
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
     *
     * @return void
     * @throws SetupException
     */
    public function setOptions(array $aOptions): void
    {
        try
        {
            $this->xConfig->setOptions($aOptions);
            // Set the library language any time the config is changed.
            $this->xTranslator->setLocale($this->xConfig->getOption('core.language'));
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
        // Set the library language any time the config is changed.
        $this->xTranslator->setLocale($this->xConfig->getOption('core.language'));
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
