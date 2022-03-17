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

use Jaxon\Exception\SetupException;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\Exception\DataDepth;
use Jaxon\Utils\Config\Exception\FileAccess;
use Jaxon\Utils\Config\Exception\FileContent;
use Jaxon\Utils\Config\Exception\FileExtension;
use Jaxon\Utils\Config\Exception\YamlExtension;
use Jaxon\Utils\Config\Reader;
use Jaxon\Utils\Translation\Translator;

class ConfigManager extends Reader
{
    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * The constructor
     *
     * @param Config $xConfig
     * @param Translator $xTranslator
     */
    public function __construct(Config $xConfig, Translator $xTranslator)
    {
        parent::__construct($xConfig);
        $this->xTranslator = $xTranslator;
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
            return parent::read($sConfigFile);
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
        }
        catch(DataDepth $e)
        {
            $sMessage = $this->xTranslator->trans('errors.data.depth',
                ['key' => $e->sPrefix, 'depth' => $e->nDepth]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Get the library config
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->xConfig;
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
