<?php

namespace Jaxon\Plugin\Code;

use Jaxon\Config\ConfigManager;
use Jaxon\Plugin\Plugin;
use Jaxon\Utils\File\Minifier;
use Jaxon\Utils\Http\UriDetector;
use Jaxon\Utils\Http\UriException;

use function rtrim;
use function is_dir;
use function is_file;
use function is_writable;
use function file_put_contents;

class AssetManager
{
    /**
     * @var ConfigManager
     */
    protected $xConfigManager;

    /**
     * @var UriDetector
     */
    private $xUriDetector;

    /**
     * @var Minifier
     */
    private $xMinifier;

    /**
     * @var bool
     */
    protected $bIncludeAllAssets;

    /**
     * Default library URL
     *
     * @var string
     */
    const JS_LIB_URL = 'https://cdn.jsdelivr.net/gh/jaxon-php/jaxon-js@3.3/dist';

    /**
     * The constructor
     *
     * @param ConfigManager $xConfigManager
     * @param UriDetector $xUriDetector
     * @param Minifier $xMinifier
     */
    public function __construct(ConfigManager $xConfigManager, UriDetector $xUriDetector, Minifier $xMinifier)
    {
        $this->xConfigManager = $xConfigManager;
        $this->xUriDetector = $xUriDetector;
        $this->xMinifier = $xMinifier;
        $this->bIncludeAllAssets = $xConfigManager->getOption('assets.include.all', true);
    }

    /**
     * Get js options
     *
     * @return string
     */
    public function getJsOptions(): string
    {
        return $this->xConfigManager->getOption('js.app.options', '');
    }

    /**
     * Check if the assets of this plugin shall be included in Jaxon generated code.
     *
     * @param Plugin $xPlugin
     *
     * @return bool
     */
    public function shallIncludeAssets(Plugin $xPlugin): bool
    {
        if($this->bIncludeAllAssets)
        {
            return true;
        }
        $sPluginOptionName = 'assets.include.' . $xPlugin->getName();
        return $this->xConfigManager->getOption($sPluginOptionName, true);
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page
     *
     * @return array
     */
    public function getJsLibFiles(): array
    {
        $sJsExtension = $this->xConfigManager->getOption('js.app.minify') ? '.min.js' : '.js';
        // The URI for the javascript library files
        $sJsLibUri = rtrim($this->xConfigManager->getOption('js.lib.uri', self::JS_LIB_URL), '/') . '/';
        // Add component files to the javascript file array;
        $aJsFiles = [$sJsLibUri . 'jaxon.core' . $sJsExtension];
        if($this->xConfigManager->getOption('core.debug.on'))
        {
            $sLanguage = $this->xConfigManager->getOption('core.language');
            $aJsFiles[] = $sJsLibUri . 'jaxon.debug' . $sJsExtension;
            $aJsFiles[] = $sJsLibUri . 'lang/jaxon.' . $sLanguage . $sJsExtension;
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
        if(!$this->xConfigManager->hasOption('core.request.uri'))
        {
            $this->xConfigManager->setOption('core.request.uri', $this->xUriDetector->detect($_SERVER));
        }
        return [
            'sResponseType'         => 'JSON',
            'sVersion'              => $this->xConfigManager->getOption('core.version'),
            'sLanguage'             => $this->xConfigManager->getOption('core.language'),
            'bLanguage'             => $this->xConfigManager->hasOption('core.language'),
            'sRequestURI'           => $this->xConfigManager->getOption('core.request.uri'),
            'sDefaultMode'          => $this->xConfigManager->getOption('core.request.mode'),
            'sDefaultMethod'        => $this->xConfigManager->getOption('core.request.method'),
            'sCsrfMetaName'         => $this->xConfigManager->getOption('core.request.csrf_meta'),
            'bDebug'                => $this->xConfigManager->getOption('core.debug.on'),
            'bVerboseDebug'         => $this->xConfigManager->getOption('core.debug.verbose'),
            'sDebugOutputID'        => $this->xConfigManager->getOption('core.debug.output_id'),
            'nResponseQueueSize'    => $this->xConfigManager->getOption('js.lib.queue_size'),
            'sStatusMessages'       => $this->xConfigManager->getOption('js.lib.show_status') ? 'true' : 'false',
            'sWaitCursor'           => $this->xConfigManager->getOption('js.lib.show_cursor') ? 'true' : 'false',
            'sDefer'                => $this->xConfigManager->getOption('js.app.options', ''),
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
        if(!$this->xConfigManager->getOption('js.app.export', false) ||
            !$this->xConfigManager->hasOption('js.app.uri') ||
            !$this->xConfigManager->hasOption('js.app.dir'))
        {
            return false;
        }
        return true;
    }

    /**
     * Write javascript files and return the corresponding URI
     *
     * @param string $sHash
     * @param string $sJsCode
     *
     * @return string
     */
    public function createJsFiles(string $sHash, string $sJsCode): string
    {
        // Check dir access
        $sJsFileName = $this->xConfigManager->getOption('js.app.file', $sHash);
        $sJsDirectory = rtrim($this->xConfigManager->getOption('js.app.dir'), '\/') . DIRECTORY_SEPARATOR;
        // - The js.app.dir must be writable
        if(!$sJsFileName || !is_dir($sJsDirectory) || !is_writable($sJsDirectory))
        {
            return '';
        }

        $sJsFilePath = $sJsDirectory . $sJsFileName . '.js';
        $sJsMinFilePath = $sJsDirectory . $sJsFileName . '.min.js';
        $sJsFileUri = rtrim($this->xConfigManager->getOption('js.app.uri'), '/') . "/$sJsFileName";
        if(!is_file($sJsFilePath) && !file_put_contents($sJsFilePath, $sJsCode))
        {
            return '';
        }
        if(!$this->xConfigManager->getOption('js.app.minify', false))
        {
            return $sJsFileUri . '.js';
        }
        if(!is_file($sJsMinFilePath) && !$this->xMinifier->minify($sJsFilePath, $sJsMinFilePath))
        {
            // If the file cannot be minified, return the plain js file.
            return $sJsFileUri . '.js';
        }
        return $sJsFileUri . '.min.js';
    }
}
