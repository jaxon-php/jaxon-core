<?php

/**
 * ConfigManager.php - Jaxon config reader
 *
 * Extends the config reader in the jaxon-config package, and provides exception handlers.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Config;

use Jaxon\App\I18n\Translator;
use Jaxon\Config\Config;
use Jaxon\Config\ConfigReader;
use Jaxon\Config\ConfigSetter;
use Jaxon\Config\Exception\DataDepth;
use Jaxon\Config\Exception\FileAccess;
use Jaxon\Config\Exception\FileContent;
use Jaxon\Config\Exception\FileExtension;
use Jaxon\Config\Exception\YamlExtension;
use Jaxon\Exception\SetupException;

use function dirname;

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
     * @var Config|null
     */
    private Config|null $xExportConfig = null;

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
    public function load(string $sConfigFile, string $sConfigSection = ''): void
    {
        try
        {
            // Read the options and save in the config.
            $this->xLibConfig = $this->xConfigSetter->setOptions($this->xLibConfig,
                $this->read($sConfigFile), $sConfigSection);
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
    public function setOption(string $sName, $xValue): void
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
    public function getOption(string $sName, $xDefault = null): mixed
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
    public function setAppOption(string $sName, $xValue): void
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
     * @param string $sNamePrefix A prefix for the config option names
     *
     * @return bool
     */
    public function setAppOptions(array $aAppOptions, string $sNamePrefix = ''): bool
    {
        try
        {
            $this->xAppConfig = $this->xConfigSetter
                ->setOptions($this->xAppConfig, $aAppOptions, $sNamePrefix);
            // Call the config change listeners.
            $this->xEventManager->appConfigChanged($this->xAppConfig, '');
            return $this->xAppConfig->changed();
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
    public function getAppOption(string $sName, $xDefault = null): mixed
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
     * Get the app options with a given prefix in a new config object
     *
     * @param string $sPrefix
     *
     * @return Config
     */
    public function getConfig(string $sPrefix): Config
    {
        return $this->xConfigSetter->newConfig($this->getAppOption($sPrefix, []));
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
     * Check if the remote logging is enabled
     *
     * @return bool
     */
    public function loggingEnabled(): bool
    {
        return $this->getAppOption('options.logging.enabled', false);
    }

    /**
     * @param string $sClassName
     *
     * @return void
     */
    public function addLibEventListener(string $sClassName): void
    {
        $this->xEventManager->addLibConfigListener($sClassName);
    }

    /**
     * @param string $sClassName
     *
     * @return void
     */
    public function addAppEventListener(string $sClassName): void
    {
        $this->xEventManager->addAppConfigListener($sClassName);
    }

    /**
     * Make the helpers functions available in the global namespace.
     *
     * @param bool $bForce
     *
     * @return void
     */
    public function globals(bool $bForce = false): void
    {
        if($bForce || $this->getAppOption('helpers.global', true))
        {
            require_once dirname(__DIR__, 2) . '/globals.php';
        }
    }

    /**
     * @return Config
     */
    public function getExportConfig(): Config
    {
        if($this->xExportConfig !== null)
        {
            return $this->xExportConfig;
        }

        // Copy the assets options in a new config object.
        return $this->xExportConfig = $this->hasAppOption('assets') ?
            $this->xConfigSetter->newConfig($this->getAppOption('assets')) :
            // Convert the options in the "lib" section to the same format as in the "app" section.
            $this->xConfigSetter->newConfig([
                'js' => $this->getOption('js.app'),
                'include' => $this->getOption('assets.include'),
            ]);
    }

    /**
     * Set the javascript or css asset
     *
     * @param bool $bExport    Whether to export the code in a file
     * @param bool $bMinify    Whether to minify the exported file
     * @param string $sUri     The URI to access the exported file
     * @param string $sDir     The directory where to create the file
     * @param string $sType    The asset type: "js" or "css"
     *
     * @return void
     */
    public function asset(bool $bExport, bool $bMinify,
        string $sUri = '', string $sDir = '', string $sType = ''): void
    {
        $sPrefix = $sType === 'js' || $sType === 'css' ? "assets.$sType" : 'assets';
        // Jaxon library settings
        $aJsOptions = [
            'export' => $bExport,
            'minify' => $bMinify,
        ];
        if($sUri !== '')
        {
            $aJsOptions['uri'] = $sUri;
        }
        if($sDir !== '')
        {
            $aJsOptions['dir'] = $sDir;
        }

        // The export options are saved in the "app" section of the config.
        $this->setAppOptions($aJsOptions, $sPrefix);
    }
}
