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

namespace Jaxon\Plugin\Code;

use Jaxon\Plugin\Code\Contracts\Generator as GeneratorContract;
use Jaxon\Utils\Template\Engine as TemplateEngine;
use Jaxon\Utils\Http\URI;

class Generator
{
    use \Jaxon\Features\Config;
    use \Jaxon\Features\Minifier;

    /**
     * Default library URL
     *
     * @var string
     */
    const JS_LIB_URL = 'https://cdn.jsdelivr.net/gh/jaxon-php/jaxon-js@3.0/dist';

    /**
     * The objects that generate code
     *
     * @var array<GeneratorContract>
     */
    protected $aGenerators = [];

    /**
     * The Jaxon template engine
     *
     * @var TemplateEngine
     */
    protected $xTemplateEngine;

    /**
     * The constructor
     *
     * @param TemplateEngine        $xTemplateEngine      The template engine
     */
    public function __construct(TemplateEngine $xTemplateEngine)
    {
        $this->xTemplateEngine = $xTemplateEngine;
    }

    /**
     * Get the correspondances between previous and current config options
     *
     * @return array
     */
    private function getOptionVars()
    {
        return [
            'sResponseType'             => 'JSON',
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
            'sDefer'                    => $this->getOption('js.app.options', ''),
        ];
    }

    /**
     * Render a template in the 'plugins' subdir
     *
     * @param string    $sTemplate      The template filename
     * @param array     $aVars          The template variables
     *
     * @return string
     */
    private function _render($sTemplate, array $aVars = [])
    {
        $aVars['sJsOptions'] = $this->getOption('js.app.options', '');
        return $this->xTemplateEngine->render("jaxon::plugins/$sTemplate", $aVars);
    }

    /**
     * Add a new generator to the list
     *
     * @param GeneratorContract     $xGenerator     The code generator
     * @param integer               $nPriority      The desired priority, used to order the plugins
     *
     * @return void
     */
    public function addGenerator(GeneratorContract $xGenerator, $nPriority)
    {
        while(isset($this->aGenerators[$nPriority]))
        {
            $nPriority++;
        }
        $this->aGenerators[$nPriority] = $xGenerator;
        // Sort the array by ascending keys
        ksort($this->aGenerators);
    }

    /**
     * Generate a hash for all the javascript code generated by the library
     *
     * @return string
     */
    private function getHash()
    {
        $sHash = jaxon()->getVersion();
        foreach($this->aGenerators as $xGenerator)
        {
            $sHash .= $xGenerator->getHash();
        }
        return md5($sHash);
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page
     *
     * @return string
     */
    public function getCss()
    {
        $sCssCode = '';
        foreach($this->aGenerators as $xGenerator)
        {
            $sCssCode = rtrim($sCssCode, " \n") . "\n" . $xGenerator->getCss();
        }
        return rtrim($sCssCode, " \n") . "\n";
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page
     *
     * @return string
     */
    public function getJs()
    {
        $sJsExtension = $this->getOption('js.app.minify') ? '.min.js' : '.js';

        // The URI for the javascript library files
        $sJsLibUri = rtrim($this->getOption('js.lib.uri', self::JS_LIB_URL), '/') . '/';
        // Add component files to the javascript file array;
        $aJsFiles = [$sJsLibUri . 'jaxon.core' . $sJsExtension];
        if($this->getOption('core.debug.on'))
        {
            $sLanguage = $this->getOption('core.language');
            $aJsFiles[] = $sJsLibUri . 'jaxon.debug' . $sJsExtension;
            $aJsFiles[] = $sJsLibUri . 'lang/jaxon.' . $sLanguage . $sJsExtension;
            /*if($this->getOption('core.debug.verbose'))
            {
                $aJsFiles[] = $sJsLibUri . 'jaxon.verbose' . $sJsExtension;
            }*/
        }
        $sJsFiles = $this->_render('includes.js', ['aUrls' => $aJsFiles]);

        $sJsCode = '';
        foreach($this->aGenerators as $xGenerator)
        {
            $sJsCode = rtrim($sJsCode, " \n") . "\n" . $xGenerator->getJs();
        }
        return $sJsFiles . "\n" . rtrim($sJsCode, " \n") . "\n";
    }

    /**
     * Get the javascript code to be sent to the browser
     *
     * @return string
     */
    private function _getScript()
    {
        $aConfigVars = $this->getOptionVars();
        $sYesScript = 'jaxon.ajax.response.process(command.response)';
        $sNoScript = 'jaxon.confirm.skip(command);jaxon.ajax.response.process(command.response)';
        $sQuestionScript = jaxon()->dialog()->confirm('msg', $sYesScript, $sNoScript);

        $aConfigVars['sQuestionScript'] = $this->_render('confirm.js', [
            'sQuestionScript' => $sQuestionScript,
        ]);

        $sScript = '';
        $sReadyScript = '';
        foreach($this->aGenerators as $xGenerator)
        {
            $sScript .= rtrim($xGenerator->getScript(), " \n") . "\n";
            if($xGenerator->readyEnabled() && !$xGenerator->readyInlined())
            {
                // Ready code which can nbe exported to an external file.
                $sReadyScript .= rtrim($xGenerator->getReadyScript(), " \n") . "\n";
            }
        }

        // These three parts are always rendered together
        return $this->_render('config.js', $aConfigVars) . "\n" . $sScript . "\n" .
            $this->_render('ready.js', ['sScript' => $sReadyScript]);
    }

    /**
     * Get the javascript code to include directly in HTML
     *
     * @return string
     */
    private function _getInlineScript()
    {
        $sScript = '';
        foreach($this->aGenerators as $xGenerator)
        {
            if($xGenerator->readyEnabled() && $xGenerator->readyInlined())
            {
                // Ready code which must be inlined in HTML.
                $sScript .= rtrim($xGenerator->getReadyScript(), " \n") . "\n";
            }
        }
        return $this->_render('ready.js', ['sScript' => $sScript]);
    }

    /**
     * Get the javascript file name
     *
     * @return void
     */
    private function getJsFileName()
    {
        // Check config options
        // - The js.app.export option must be set to true
        // - The js.app.uri and js.app.dir options must be set to non null values
        if(!$this->getOption('js.app.export') ||
            !$this->getOption('js.app.uri') ||
            !$this->getOption('js.app.dir'))
        {
            return '';
        }

        // The file name
        return $this->hasOption('js.app.file') ? $this->getOption('js.app.file') : $this->getHash();
    }

    /**
     * Write javascript files and return the corresponding URI
     *
     * @return string
     */
    public function createFiles($sJsDirectory, $sJsFileName)
    {
        // Check dir access
        // - The js.app.dir must be writable
        if(!$sJsFileName || !is_dir($sJsDirectory) || !is_writable($sJsDirectory))
        {
            return '';
        }

        $sOutFile = $sJsFileName . '.js';
        $sMinFile = $sJsFileName . '.min.js';
        if(!is_file($sJsDirectory . $sOutFile))
        {
            if(!file_put_contents($sJsDirectory . $sOutFile, $this->_getScript()))
            {
                return '';
            }
        }
        if(($this->getOption('js.app.minify')) && !is_file($sJsDirectory . $sMinFile))
        {
            if(!$this->minify($sJsDirectory . $sOutFile, $sJsDirectory . $sMinFile))
            {
                return '';
            }
        }

        $sJsAppUri = rtrim($this->getOption('js.app.uri'), '/') . '/';
        $sJsExtension = $this->getOption('js.app.minify') ? '.min.js' : '.js';
        return $sJsAppUri . $sJsFileName . $sJsExtension;
    }

    /**
     * Get the javascript code to be sent to the browser
     *
     * @param boolean        $bIncludeJs         Also get the JS files
     * @param boolean        $bIncludeCss        Also get the CSS files
     *
     * @return string
     */
    public function getScript($bIncludeJs, $bIncludeCss)
    {
        if(!$this->getOption('core.request.uri'))
        {
            $this->setOption('core.request.uri', jaxon()->di()->get(URI::class)->detect());
        }

        $sScript = '';
        if(($bIncludeCss))
        {
            $sScript .= $this->getCss() . "\n";
        }
        if(($bIncludeJs))
        {
            $sScript .= $this->getJs() . "\n";
        }

        $sJsDirectory = rtrim($this->getOption('js.app.dir'), '/') . '/';
        $sUrl = $this->createFiles($sJsDirectory, $this->getJsFileName());
        if(($sUrl))
        {
            return $sScript . $this->_render('include.js', ['sUrl' => $sUrl]) . "\n" .
                $this->_render('wrapper.js', ['sScript' => $this->_getInlineScript()]);
        }

        return $sScript . $this->_render('wrapper.js', [
            'sScript' => $this->_getScript() . "\n" . $this->_getInlineScript()
        ]);
    }
}
