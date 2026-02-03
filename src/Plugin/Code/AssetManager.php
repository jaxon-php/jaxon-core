<?php

/**
 * AssetManager.php
 *
 * Generate static files for Jaxon CSS and Javascript codes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Code;

use Jaxon\App\Config\ConfigManager;
use Jaxon\Config\Config;
use Jaxon\Plugin\AbstractPlugin;
use Jaxon\Plugin\CodeGeneratorInterface as Generator;
use Jaxon\Plugin\CssCodeGeneratorInterface as CssGenerator;
use Jaxon\Plugin\JsCodeGeneratorInterface as JsGenerator;
use Jaxon\Storage\StorageManager;
use Lagdo\Facades\Logger;
use League\Flysystem\Filesystem;
use Closure;
use Throwable;

use function implode;
use function is_array;
use function is_string;
use function is_subclass_of;
use function rtrim;
use function trim;

class AssetManager
{
    /**
     * @var array<Filesystem>
     */
    protected array $aStorage = [];

    /**
     * Default library URL
     *
     * @var string
     */
    private const JS_LIB_URL = 'https://cdn.jsdelivr.net/gh/jaxon-php/jaxon-js@5.2.2/dist';

    /**
     * The constructor
     *
     * @param ConfigManager $xConfigManager
     * @param StorageManager $xStorageManager
     * @param MinifierInterface $xMinifier
     */
    public function __construct(private ConfigManager $xConfigManager,
        private StorageManager $xStorageManager, private MinifierInterface $xMinifier)
    {}

    /**
     * @return Config
     */
    protected function config(): Config
    {
        return $this->xConfigManager->getExportConfig();
    }

    /**
     * @param string $sAsset "js" or "css"
     *
     * @return Filesystem
     */
    protected function _storage(string $sAsset): Filesystem
    {
        if($this->config()->hasOption('storage'))
        {
            return $this->xStorageManager->get($this->config()->getOption('storage'));
        }

        $sRootDir = $this->getAssetDir($sAsset);
        // Fylsystem options: we don't want the root dir to be created if it doesn't exist.
        $aAdapterOptions = ['lazyRootCreation' => true];
        $aDirOptions = [
            'config' => [
                'public_url' => $this->getAssetUri($sAsset),
            ],
        ];
        return $this->xStorageManager
            ->adapter('local', $aAdapterOptions)
            ->make($sRootDir, $aDirOptions);
    }

    /**
     * @param string $sAsset "js" or "css"
     *
     * @return Filesystem
     */
    protected function storage(string $sAsset): Filesystem
    {
        return $this->aStorage[$sAsset] ??= $this->_storage($sAsset);
    }

    /**
     * @param array $aValues
     *
     * @return string
     */
    public function makeFileOptions(array $aValues): string
    {
        if(!isset($aValues['options']) || !$aValues['options'])
        {
            return '';
        }
        if(is_array($aValues['options']))
        {
            $aOptions = [];
            foreach($aValues['options'] as $sName => $sValue)
            {
                $aOptions[] = "{$sName}=\"" . trim($sValue) . '"';
            }
            return implode(' ', $aOptions);
        }
        if(is_string($aValues['options']))
        {
            return trim($aValues['options']);
        }
        return '';
    }

    /**
     * Get app js options
     *
     * @return string
     */
    public function getJsOptions(): string
    {
        // Revert to the options in the "lib" section in the config,
        // if there is no options defined in the 'app' section.
        if(!$this->xConfigManager->hasAppOption('assets'))
        {
            $sOptions = trim($this->config()->getOption('js.options', ''));
            return $sOptions === '' ? 'charset="UTF-8"' : "$sOptions charset=\"UTF-8\"";
        }

        return $this->makeFileOptions([
            'options' => $this->config()->getOption('js.options', ''),
        ]);
    }

    /**
     * Get app js options
     *
     * @return string
     */
    public function getCssOptions(): string
    {
        return $this->makeFileOptions([
            'options' => $this->config()->getOption('css.options', ''),
        ]);
    }

    /**
     * Check if the assets of this plugin shall be included in Jaxon generated code.
     *
     * @param Generator|CssGenerator|JsGenerator $xGenerator
     *
     * @return bool
     */
    public function shallIncludeAssets(Generator|CssGenerator|JsGenerator $xGenerator): bool
    {
        if(!is_subclass_of($xGenerator, AbstractPlugin::class))
        {
            return true;
        }

        /** @var AbstractPlugin */
        $xPlugin = $xGenerator;
        $sPluginOptionName = 'include.' . $xPlugin->getName();

        return $this->config()->hasOption($sPluginOptionName) ?
            $this->config()->getOption($sPluginOptionName) :
            $this->config()->getOption('include.all', true);
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page
     *
     * @return array
     */
    public function getJsLibUrls(): array
    {
        $sJsExtension = $this->config()->getOption('minify') ? '.min.js' : '.js';
        // The URI for the javascript library files
        $sJsLibUri = $this->xConfigManager->getOption('js.lib.uri', self::JS_LIB_URL);
        $sJsLibUri = rtrim($sJsLibUri, '/');

        // Add component files to the javascript file array.
        $sChibiUrl = "$sJsLibUri/libs/chibi/chibi$sJsExtension";
        $aJsUrls = [
            $this->xConfigManager->getOption('js.lib.jq', $sChibiUrl),
            "$sJsLibUri/jaxon.core$sJsExtension",
        ];
        if($this->xConfigManager->getOption('core.debug.on'))
        {
            $sLanguage = $this->xConfigManager->getOption('core.language');
            $aJsUrls[] = "$sJsLibUri/jaxon.debug$sJsExtension";
            $aJsUrls[] = "$sJsLibUri/lang/jaxon.$sLanguage$sJsExtension";
        }

        return $aJsUrls;
    }

    /**
     * @param string $sAsset "js" or "css"
     *
     * @return string
     */
    private function getAssetUri(string $sAsset): string
    {
        return rtrim($this->config()->hasOption("$sAsset.uri") ?
            $this->config()->getOption("$sAsset.uri") :
            $this->config()->getOption('uri', ''), '/');
    }

    /**
     * @param string $sAsset "js" or "css"
     *
     * @return string
     */
    private function getAssetDir(string $sAsset): string
    {
        return rtrim($this->config()->hasOption("$sAsset.dir") ?
            $this->config()->getOption("$sAsset.dir") :
            $this->config()->getOption('dir', ''), '/\/');
    }

    /**
     * @param Closure $cGetHash
     * @param string $sAsset "js" or "css"
     *
     * @return string
     */
    private function getAssetFile(Closure $cGetHash, string $sAsset): string
    {
        return $this->config()->hasOption("$sAsset.file") ?
            $this->config()->getOption("$sAsset.file") :
            // Make sure to call invoke callback only if required.
            ($this->config()->hasOption("file") ?
            $this->config()->getOption("file") : $cGetHash());
    }

    /**
     * @param string $sAsset "js" or "css"
     *
     * @return bool
     */
    private function minifyEnabled(string $sAsset): bool
    {
        return $this->config()->hasOption("$sAsset.minify") ?
            $this->config()->getOption("$sAsset.minify") :
            $this->config()->getOption('minify', false);
    }

    /**
     * @param string $sAsset "js" or "css"
     *
     * @return bool
     */
    private function exportEnabled(string $sAsset): bool
    {
        return $this->config()->hasOption("$sAsset.export") ?
            $this->config()->getOption("$sAsset.export") :
            $this->config()->getOption('export', false);
    }

    /**
     * @param Filesystem $xStorage
     * @param string $sFilePath
     *
     * @return bool
     */
    private function fileExists(Filesystem $xStorage, string $sFilePath): bool
    {
        try
        {
            return $xStorage->fileExists($sFilePath);
        }
        catch(Throwable $e)
        {
            Logger::warning("Unable to check asset file at $sFilePath.", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * @param Filesystem $xStorage
     * @param string $sFilePath
     * @param string $sContent
     *
     * @return bool
     */
    private function writeFile(Filesystem $xStorage, string $sFilePath, string $sContent): bool
    {
        try
        {
            $xStorage->write($sFilePath, $sContent);
            return true;
        }
        catch(Throwable $e)
        {
            Logger::warning("Unable to write to asset file at $sFilePath.", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * @param string $sAsset "js" or "css"
     * @param string $sFilePath
     * @param string $sMinFilePath
     *
     * @return bool
     */
    private function minifyAsset(string $sAsset, string $sFilePath, string $sMinFilePath): bool
    {
        if(!$this->minifyEnabled($sAsset))
        {
            return false;
        }

        $xStorage = $this->storage($sAsset);
        if($xStorage->fileExists($sMinFilePath))
        {
            return true;
        }

        $sMinContent = $sAsset === 'js' ?
            $this->xMinifier->minifyJsCode($xStorage->read($sFilePath)) :
            $this->xMinifier->minifyCssCode($xStorage->read($sFilePath));
        if($sMinContent === false || $sMinContent === '')
        {
            return false;
        }

        return $this->writeFile($xStorage, $sMinFilePath, $sMinContent);
    }

    private function getPublicUrl(string $sFilePath, string $sAsset): string
    {
        $sUri = $this->getAssetUri($sAsset);
        return $sUri !== '' ? "$sUri/$sFilePath" :
            $this->storage($sAsset)->publicUrl($sFilePath);
    }

    /**
     * Write javascript or css files and return the corresponding URI
     *
     * @param Closure $cGetHash
     * @param Closure $cGetCode
     * @param string $sAsset "js" or "css"
     *
     * @return string
     */
    public function createFiles(Closure $cGetHash, Closure $cGetCode, string $sAsset): string
    {
        // Check if the config options allow the file creation.
        // - The assets.js.export option must be set to true
        // - The assets.js.uri and assets.js.dir options must be set to non null values
        if(!$this->exportEnabled($sAsset) ||
            // $this->getAssetUri($sAsset) === '' ||
            $this->getAssetDir($sAsset) === '')
        {
            return '';
        }

        // Check dir access
        $xStorage = $this->storage($sAsset);
        $sFileName = $this->getAssetFile($cGetHash, $sAsset);
        // - The assets.js.dir must be writable
        if(!$sFileName || !$xStorage->directoryExists('') /*|| $xStorage->visibility('') !== 'public'*/)
        {
            return '';
        }

        $sFilePath = "{$sFileName}.{$sAsset}";
        $sMinFilePath = "{$sFileName}.min.{$sAsset}";

        // Try to create the file and write the code, if it doesn't exist.
        if(!$this->fileExists($xStorage, $sFilePath) &&
            !$this->writeFile($xStorage, $sFilePath, $cGetCode()))
        {
            return '';
        }

        if(!$this->minifyEnabled($sAsset))
        {
            return $this->getPublicUrl($sFilePath, $sAsset);
        }

        // If the file cannot be minified, return the plain js file.
        return $this->minifyAsset($sAsset, $sFilePath, $sMinFilePath) ?
            $this->getPublicUrl($sMinFilePath, $sAsset) :
            $this->getPublicUrl($sFilePath, $sAsset);
    }

    /**
     * Write javascript files and return the corresponding URI
     *
     * @param Closure $cGetHash
     * @param Closure $cGetCode
     *
     * @return string
     */
    public function createJsFiles(Closure $cGetHash, Closure $cGetCode): string
    {
        // Using closures, so the code generator is actually called only if it is really required.
        return $this->createFiles($cGetHash, $cGetCode, 'js');
    }

    /**
     * Write javascript files and return the corresponding URI
     *
     * @param Closure $cGetHash
     * @param Closure $cGetCode
     *
     * @return string
     */
    public function createCssFiles(Closure $cGetHash, Closure $cGetCode): string
    {
        // Using closures, so the code generator is actually called only if it is really required.
        return $this->createFiles($cGetHash, $cGetCode, 'css');
    }
}
