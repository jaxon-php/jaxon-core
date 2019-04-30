<?php

/**
 * Generator.php - Jaxon code generator
 *
 * Generate HTML, CSS and Javascript code for Jaxon.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Code;

use Jaxon\Plugin\Manager;

class Generator
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Cache;
    use \Jaxon\Utils\Traits\Minifier;
    use \Jaxon\Utils\Traits\Template;

    /**
     * The response type.
     *
     * @var string
     */
    const RESPONSE_TYPE = 'JSON';

    /**
     * The plugin manager
     *
     * @var Manager
     */
    protected $xPluginManager;

    /**
     * Generated CSS code
     *
     * @var string|null
     */
    protected $sCssCode = null;

    /**
     * Generated Javascript code
     *
     * @var string|null
     */
    protected $sJsCode = null;

    /**
     * Generated Javascript ready script
     *
     * @var string|null
     */
    protected $sJsReady = null;

    /**
     * The constructor
     *
     * @param Manager    $xPluginManager
     */
    public function __construct(Manager $xPluginManager)
    {
        $this->xPluginManager = $xPluginManager;
    }

    /**
     * Get the base URI of the Jaxon library javascript files
     *
     * @return string
     */
    private function getJsLibUri()
    {
        if(!$this->hasOption('js.lib.uri'))
        {
            // return 'https://cdn.jsdelivr.net/jaxon/1.2.0/';
            return 'https://cdn.jsdelivr.net/gh/jaxon-php/jaxon-js@2.0/dist/';
        }
        // Todo: check the validity of the URI
        return rtrim($this->getOption('js.lib.uri'), '/') . '/';
    }

    /**
     * Get the extension of the Jaxon library javascript files
     *
     * The returned string is '.min.js' if the files are minified.
     *
     * @return string
     */
    private function getJsLibExt()
    {
        // $jsDelivrUri = 'https://cdn.jsdelivr.net';
        // $nLen = strlen($jsDelivrUri);
        // The jsDelivr CDN only hosts minified files
        // if(($this->getOption('js.app.minify')) || substr($this->getJsLibUri(), 0, $nLen) == $jsDelivrUri)
        // Starting from version 2.0.0 of the js lib, the jsDelivr CDN also hosts non minified files.
        if(($this->getOption('js.app.minify')))
        {
            return '.min.js';
        }
        return '.js';
    }

    /**
     * Check if the javascript code generated by Jaxon can be exported to an external file
     *
     * @return boolean
     */
    public function canExportJavascript()
    {
        // Check config options
        // - The js.app.extern option must be set to true
        // - The js.app.uri and js.app.dir options must be set to non null values
        if(!$this->getOption('js.app.extern') ||
            !$this->getOption('js.app.uri') ||
            !$this->getOption('js.app.dir'))
        {
            return false;
        }
        // Check dir access
        // - The js.app.dir must be writable
        $sJsAppDir = $this->getOption('js.app.dir');
        if(!is_dir($sJsAppDir) || !is_writable($sJsAppDir))
        {
            return false;
        }
        return true;
    }

    /**
     * Set the cache directory for the template engine
     *
     * @return void
     */
    private function setTemplateCacheDir()
    {
        if($this->hasOption('core.template.cache_dir'))
        {
            $this->setCacheDir($this->getOption('core.template.cache_dir'));
        }
    }

    /**
     * Generate a hash for all the javascript code generated by the library
     *
     * @return string
     */
    private function generateHash()
    {
        $sHash = jaxon()->getVersion();
        foreach($this->xPluginManager->getRequestPlugins() as $xPlugin)
        {
            $sHash .= $xPlugin->generateHash();
        }
        foreach($this->xPluginManager->getResponsePlugins() as $xPlugin)
        {
            $sHash .= $xPlugin->generateHash();
        }
        return md5($sHash);
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page
     *
     * @return string
     */
    private function makePluginsCode()
    {
        if($this->sCssCode === null || $this->sJsCode === null || $this->sJsReady === null)
        {
            $this->sCssCode = '';
            $this->sJsCode = '';
            $this->sJsReady = '';
            foreach($this->xPluginManager->getResponsePlugins() as $xPlugin)
            {
                if(($sCssCode = trim($xPlugin->getCss())))
                {
                    $this->sCssCode .= rtrim($sCssCode, " \n") . "\n";
                }
                if(($sJsCode = trim($xPlugin->getJs())))
                {
                    $this->sJsCode .= rtrim($sJsCode, " \n") . "\n";
                }
                if(($sJsReady = trim($xPlugin->getScript())))
                {
                    $this->sJsReady .= trim($sJsReady, " \n") . "\n";
                }
            }

            $this->sJsReady = $this->render('jaxon::plugins/ready.js', ['sPluginScript' => $this->sJsReady]);
            foreach($this->xPluginManager->getRequestPlugins() as $xPlugin)
            {
                if(($sJsReady = trim($xPlugin->getScript())))
                {
                    $this->sJsReady .= trim($sJsReady, " \n") . "\n";
                }
            }

            foreach($this->xPluginManager->getPackages() as $sClass)
            {
                $xPackage = jaxon()->di()->get($sClass);
                if(($sCssCode = trim($xPackage->css())))
                {
                    $this->sCssCode .= rtrim($sCssCode, " \n") . "\n";
                }
                if(($sJsCode = trim($xPackage->js())))
                {
                    $this->sJsCode .= rtrim($sJsCode, " \n") . "\n";
                }
                $xPackage = jaxon()->di()->get($sClass);
                if(($sJsReady = trim($xPackage->ready())))
                {
                    $this->sJsReady .= trim($sJsReady, " \n") . "\n";
                }
            }
        }
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page
     *
     * @return string
     */
    public function getJs()
    {
        $sJsLibUri = $this->getJsLibUri();
        $sJsLibExt = $this->getJsLibExt();
        $sJsCoreUrl = $sJsLibUri . 'jaxon.core' . $sJsLibExt;
        $sJsDebugUrl = $sJsLibUri . 'jaxon.debug' . $sJsLibExt;
        // $sJsVerboseUrl = $sJsLibUri . 'jaxon.verbose' . $sJsLibExt;
        $sJsLanguageUrl = $sJsLibUri . 'lang/jaxon.' . $this->getOption('core.language') . $sJsLibExt;

        // Add component files to the javascript file array;
        $aJsFiles = array($sJsCoreUrl);
        if($this->getOption('core.debug.on'))
        {
            $aJsFiles[] = $sJsDebugUrl;
            $aJsFiles[] = $sJsLanguageUrl;
            /*if($this->getOption('core.debug.verbose'))
            {
                $aJsFiles[] = $sJsVerboseUrl;
            }*/
        }

        // Set the template engine cache dir
        $this->setTemplateCacheDir();
        $this->makePluginsCode();

        return $this->render('jaxon::plugins/includes.js', [
            'sJsOptions' => $this->getOption('js.app.options'),
            'aUrls' => $aJsFiles,
        ]) . $this->sJsCode;
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page
     *
     * @return string
     */
    public function getCss()
    {
        // Set the template engine cache dir
        $this->setTemplateCacheDir();
        $this->makePluginsCode();

        return $this->sCssCode;
    }

    /**
     * Get the correspondances between previous and current config options
     *
     * They are used to keep the deprecated config options working.
     * They will be removed when the deprecated options will lot be supported anymore.
     *
     * @return array
     */
    private function getOptionVars()
    {
        return [
            'sResponseType'             => self::RESPONSE_TYPE,
            'sVersion'                  => $this->getOption('core.version'),
            'sLanguage'                 => $this->getOption('core.language'),
            'bLanguage'                 => $this->hasOption('core.language') ? true : false,
            'sRequestURI'               => $this->getOption('core.request.uri'),
            'sDefaultMode'              => $this->getOption('core.request.mode'),
            'sDefaultMethod'            => $this->getOption('core.request.method'),
            'sCsrfMetaName'             => $this->getOption('core.request.csrf_meta'),
            'bDebug'                    => $this->getOption('core.debug.on'),
            'bVerboseDebug'             => $this->getOption('core.debug.verbose'),
            'sDebugOutputID'            => $this->getOption('core.debug.output_id'),
            'nResponseQueueSize'        => $this->getOption('js.lib.queue_size'),
            'sStatusMessages'           => $this->getOption('js.lib.show_status') ? 'true' : 'false',
            'sWaitCursor'               => $this->getOption('js.lib.show_cursor') ? 'true' : 'false',
            'sDefer'                    => $this->getOption('js.app.options'),
        ];
    }

    /**
     * Get the javascript code to be sent to the browser
     *
     * @return string
     */
    private function _getScript()
    {
        $aVars = $this->getOptionVars();
        $sYesScript = 'jaxon.ajax.response.process(command.response)';
        $sNoScript = 'jaxon.confirm.skip(command);jaxon.ajax.response.process(command.response)';
        $sConfirmScript = jaxon()->dialog()->confirm('msg', $sYesScript, $sNoScript);
        $aVars['sConfirmScript'] = $this->render('jaxon::plugins/confirm.js', ['sConfirmScript' => $sConfirmScript]);

        return $this->render('jaxon::plugins/config.js', $aVars) . "\n" . $this->sJsReady . "\n";
    }

    /**
     * Get the javascript code to be sent to the browser
     *
     * Also call each of the request plugins giving them the opportunity
     * to output some javascript to the page being generated.
     * This is called only when the page is being loaded initially.
     * This is not called when processing a request.
     *
     * @return string
     */
    public function getScript()
    {
        // Set the template engine cache dir
        $this->setTemplateCacheDir();
        $this->makePluginsCode();

        if($this->canExportJavascript())
        {
            $sJsAppURI = rtrim($this->getOption('js.app.uri'), '/') . '/';
            $sJsAppDir = rtrim($this->getOption('js.app.dir'), '/') . '/';

            // The plugins scripts are written into the javascript app dir
            $sHash = $this->generateHash();
            $sOutFile = $sHash . '.js';
            $sMinFile = $sHash . '.min.js';
            if(!is_file($sJsAppDir . $sOutFile))
            {
                file_put_contents($sJsAppDir . $sOutFile, $this->_getScript());
            }
            if(($this->getOption('js.app.minify')) && !is_file($sJsAppDir . $sMinFile))
            {
                if(($this->minify($sJsAppDir . $sOutFile, $sJsAppDir . $sMinFile)))
                {
                    $sOutFile = $sMinFile;
                }
            }

            // The returned code loads the generated javascript file
            $sScript = $this->render('jaxon::plugins/include.js', array(
                'sJsOptions' => $this->getOption('js.app.options'),
                'sUrl' => $sJsAppURI . $sOutFile,
            ));
        }
        else
        {
            // The plugins scripts are wrapped with javascript tags
            $sScript = $this->render('jaxon::plugins/wrapper.js', array(
                'sJsOptions' => $this->getOption('js.app.options'),
                'sScript' => $this->_getScript(),
            ));
        }

        return $sScript;
    }
}
