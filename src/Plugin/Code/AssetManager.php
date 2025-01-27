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
use Jaxon\App\Config\ConfigTrait;
use Jaxon\Plugin\AbstractPlugin;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Utils\Http\UriException;

use function file_put_contents;
use function is_dir;
use function is_file;
use function is_writable;
use function rtrim;

class AssetManager
{
    use ConfigTrait;

    /**
     * Default library URL
     *
     * @var string
     */
    const JS_LIB_URL = 'https://cdn.jsdelivr.net/gh/jaxon-php/jaxon-js@5.0.0-beta.21/dist';

    /**
     * The constructor
     *
     * @param ConfigManager $xConfigManager
     * @param ParameterReader $xParameterReader
     * @param MinifierInterface $xMinifier
     */
    public function __construct(private ConfigManager $xConfigManager,
        private ParameterReader $xParameterReader, private MinifierInterface $xMinifier)
    {
        $this->xConfigManager = $xConfigManager;
        $this->xParameterReader = $xParameterReader;
        $this->xMinifier = $xMinifier;
    }

    /**
     * @return ConfigManager
     */
    protected function config(): ConfigManager
    {
        return $this->xConfigManager;
    }

    /**
     * Get app js options
     *
     * @return string
     */
    public function getJsOptions(): string
    {
        return $this->getLibOption('js.app.options', '');
    }

    /**
     * Check if the assets of this plugin shall be included in Jaxon generated code.
     *
     * @param AbstractPlugin $xPlugin
     *
     * @return bool
     */
    public function shallIncludeAssets(AbstractPlugin $xPlugin): bool
    {
        $sPluginOptionName = 'assets.include.' . $xPlugin->getName();
        if($this->hasLibOption($sPluginOptionName))
        {
            return $this->getLibOption($sPluginOptionName);
        }
        return $this->getLibOption('assets.include.all', true);
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page
     *
     * @return array
     */
    public function getJsLibFiles(): array
    {
        $sJsExtension = $this->getLibOption('js.app.minify') ? '.min.js' : '.js';
        // The URI for the javascript library files
        $sJsLibUri = $this->getLibOption('js.lib.uri', self::JS_LIB_URL);
        $sJsLibUri = rtrim($sJsLibUri, '/');

        // Add component files to the javascript file array;
        $aJsFiles = [
            $this->getLibOption('js.lib.jq', "$sJsLibUri/libs/chibi/chibi$sJsExtension"),
            "$sJsLibUri/jaxon.core$sJsExtension",
        ];
        if($this->getLibOption('core.debug.on'))
        {
            $sLanguage = $this->getLibOption('core.language');
            $aJsFiles[] = "$sJsLibUri/jaxon.debug$sJsExtension";
            $aJsFiles[] = "$sJsLibUri/lang/jaxon.$sLanguage$sJsExtension";
        }

        return $aJsFiles;
    }

    /**
     * Get the mappings between previous and current config options
     *
     * @return array
     * @throws UriException
     */
    public function getOptionVars(): array
    {
        if(!$this->hasLibOption('core.request.uri'))
        {
            $this->setLibOption('core.request.uri', $this->xParameterReader->uri());
        }
        return [
            'sResponseType'         => 'JSON',
            'sVersion'              => $this->getLibOption('core.version'),
            'sLanguage'             => $this->getLibOption('core.language'),
            'bLanguage'             => $this->hasLibOption('core.language'),
            'sRequestURI'           => $this->getLibOption('core.request.uri'),
            'sDefaultMode'          => $this->getLibOption('core.request.mode'),
            'sDefaultMethod'        => $this->getLibOption('core.request.method'),
            'sCsrfMetaName'         => $this->getLibOption('core.request.csrf_meta'),
            'bDebug'                => $this->getLibOption('core.debug.on'),
            'bVerboseDebug'         => $this->getLibOption('core.debug.verbose'),
            'sDebugOutputID'        => $this->getLibOption('core.debug.output_id'),
            'nResponseQueueSize'    => $this->getLibOption('js.lib.queue_size'),
            'sStatusMessages'       => $this->getLibOption('js.lib.show_status') ? 'true' : 'false',
            'sWaitCursor'           => $this->getLibOption('js.lib.show_cursor') ? 'true' : 'false',
            'sDefer'                => $this->getLibOption('js.app.options', ''),
        ];
    }

    /**
     * Get the javascript file name
     *
     * @return bool
     */
    public function shallCreateJsFiles(): bool
    {
        // Check config options
        // - The js.app.export option must be set to true
        // - The js.app.uri and js.app.dir options must be set to non null values
        if(!$this->getLibOption('js.app.export', false) ||
            !$this->getLibOption('js.app.uri') || !$this->getLibOption('js.app.dir'))
        {
            return false;
        }
        return true;
    }

    /**
     * Write javascript files and return the corresponding URI
     *
     * @param CodeGenerator $codeGenerator
     * @param string $sJsScript
     *
     * @return string
     */
    public function createJsFiles(CodeGenerator $xCodeGenerator, string $sJsScript): string
    {
        // Check dir access
        $sJsFileName = $this->getLibOption('js.app.file') ?: $xCodeGenerator->getHash();
        $sJsDirectory = rtrim($this->getLibOption('js.app.dir'), '\/') . DIRECTORY_SEPARATOR;
        // - The js.app.dir must be writable
        if(!$sJsFileName || !is_dir($sJsDirectory) || !is_writable($sJsDirectory))
        {
            return '';
        }

        $sJsFilePath = $sJsDirectory . $sJsFileName . '.js';
        $sJsMinFilePath = $sJsDirectory . $sJsFileName . '.min.js';
        $sJsFileUri = rtrim($this->getLibOption('js.app.uri'), '/') . "/$sJsFileName";

        if(!is_file($sJsFilePath) &&
            !@file_put_contents($sJsFilePath, $sJsScript))
        {
            return '';
        }
        if(!$this->getLibOption('js.app.minify', false))
        {
            return $sJsFileUri . '.js';
        }
        if(!is_file($sJsMinFilePath) &&
            !$this->xMinifier->minify($sJsFilePath, $sJsMinFilePath))
        {
            // If the file cannot be minified, return the plain js file.
            return $sJsFileUri . '.js';
        }
        return $sJsFileUri . '.min.js';
    }
}
