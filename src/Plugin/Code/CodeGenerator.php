<?php

/**
 * CodeGenerator.php - Jaxon code generator
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

use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Plugin\AbstractPlugin;
use Jaxon\Utils\Http\UriException;
use Jaxon\Utils\Template\TemplateEngine;

use function array_reduce;
use function is_subclass_of;
use function ksort;
use function md5;
use function trim;

class CodeGenerator
{
    /**
     * @var AssetManager
     */
    private $xAssetManager;

    /**
     * The classes that generate code
     *
     * @var array<string>
     */
    protected $aCodeGenerators = [];

    /**
     * @var string
     */
    protected $sJsOptions;

    /**
     * @var string
     */
    protected $sCss = '';

    /**
     * @var string
     */
    protected $sJs = '';

    /**
     * @var string
     */
    protected $sJsScript = '';

    /**
     * @var string
     */
    protected $sJsReadyScript = '';

    /**
     * @var string
     */
    protected $sJsInlineScript = '';

    /**
     * @var string
     */
    protected $bGenerated = false;

    /**
     * The constructor
     *
     * @param string $sVersion
     * @param Container $di
     * @param Translator $xTranslator
     * @param TemplateEngine $xTemplateEngine
     */
    public function __construct(private string $sVersion, private Container $di,
        private Translator $xTranslator, private TemplateEngine $xTemplateEngine)
    {}

    /**
     * Add a code generator to the list
     *
     * @param string $sClassName    The code generator class
     * @param int $nPriority    The desired priority, used to order the plugins
     *
     * @return void
     */
    public function addCodeGenerator(string $sClassName, int $nPriority)
    {
        while(isset($this->aCodeGenerators[$nPriority]))
        {
            $nPriority++;
        }
        $this->aCodeGenerators[$nPriority] = $sClassName;
    }

    /**
     * Generate a hash for all the javascript code generated by the library
     *
     * @return string
     */
    public function getHash(): string
    {
        return md5(array_reduce($this->aCodeGenerators,
            function($sHash, $sClassName) {
                return $sHash . $this->di->g($sClassName)->getHash();
            }, $this->sVersion));
    }

    /**
     * Render a template in the 'plugins' subdir
     *
     * @param string $sTemplate    The template filename
     * @param array $aVars    The template variables
     *
     * @return string
     */
    private function render(string $sTemplate, array $aVars = []): string
    {
        $aVars['sJsOptions'] = $this->sJsOptions;
        return $this->xTemplateEngine->render("jaxon::plugins/$sTemplate", $aVars);
    }

    /**
     * Generate the Jaxon CSS and js codes for a given plugin
     *
     * @param string $sClassName
     *
     * @return void
     */
    private function generatePluginCodes(string $sClassName)
    {
        $xGenerator = $this->di->g($sClassName);
        if(!is_subclass_of($xGenerator, AbstractPlugin::class) ||
            $this->xAssetManager->shallIncludeAssets($xGenerator))
        {
            // HTML tags for CSS
            $this->sCss = trim($this->sCss) . "\n" . trim($xGenerator->getCss(), " \n");
            // HTML tags for js
            $this->sJs = trim($this->sJs) . "\n" . trim($xGenerator->getJs(), " \n");
        }
        // Javascript code
        $this->sJsScript = trim($this->sJsScript) . "\n\n" . trim($xGenerator->getScript(), " \n");
        if($xGenerator->readyEnabled())
        {
            $sScriptAttr = $xGenerator->readyInlined() ? 'sJsInlineScript' : 'sJsReadyScript';
            $this->$sScriptAttr = trim($this->$sScriptAttr) . "\n\n" .
                trim($xGenerator->getReadyScript(), " \n");
        }
    }

    /**
     * @return string
     */
    private function getJsInit(): string
    {
        return '
    jaxon.processCustomAttrs();
    jaxon.labels && jaxon.labels(' . json_encode($this->xTranslator->translations('labels')) . ');
';
    }

    /**
     * Render the generated CSS ans js codes
     *
     * @return void
     */
    private function renderCodes()
    {
        $this->sCss = trim($this->sCss, " \n");
        $this->sJs = trim($this->sJs, " \n");
        $this->sJsScript = trim($this->sJsScript, " \n");
        $this->sJsReadyScript = $this->render('ready.js', [
            'sScript' => $this->getJsInit() . trim($this->sJsReadyScript, " \n"),
        ]);
        $this->sJsInlineScript = trim($this->sJsInlineScript, " \n");
        if(($this->sJsInlineScript))
        {
            $this->sJsInlineScript = $this->render('ready.js', [
                'sScript' => $this->sJsInlineScript . "\n",
            ]);
        }
        // Prepend Jaxon javascript files to HTML tags for Js
        $aJsFiles = $this->xAssetManager->getJsLibFiles();
        $this->sJs = trim($this->render('includes.js', [
            'aUrls' => $aJsFiles,
        ])) . "\n\n" . $this->sJs;
    }

    /**
     * Generate the Jaxon CSS ans js codes
     *
     * @return void
     * @throws UriException
     */
    private function generateCodes()
    {
        if($this->bGenerated)
        {
            return;
        }

        // Sort the code generators by ascending priority
        ksort($this->aCodeGenerators);

        $this->xAssetManager = $this->di->getAssetManager();
        $this->sJsOptions = $this->xAssetManager->getJsOptions();
        foreach($this->aCodeGenerators as $sClassName)
        {
            $this->generatePluginCodes($sClassName);
        }
        $this->renderCodes();

        $sJsConfigVars = $this->render('config.js', $this->xAssetManager->getOptionVars());
        // These three parts are always rendered together
        $this->sJsScript = trim($sJsConfigVars) . "\n\n" .
            trim($this->sJsScript) . "\n\n" . trim($this->sJsReadyScript);

        // The codes are already generated.
        $this->bGenerated = true;
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page
     *
     * @return string
     * @throws UriException
     */
    public function getCss(): string
    {
        $this->generateCodes();
        return $this->sCss;
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page
     *
     * @return string
     * @throws UriException
     */
    public function getJs(): string
    {
        $this->generateCodes();
        return $this->sJs;
    }

    /**
     * Get the generated javascript code
     *
     * @return string
     */
    public function getJsScript(): string
    {
        return $this->sJsScript;
    }

    /**
     * Get the javascript code to be sent to the browser
     *
     * @param bool $bIncludeJs Also get the JS files
     * @param bool $bIncludeCss Also get the CSS files
     *
     * @return string
     * @throws UriException
     */
    public function getScript(bool $bIncludeJs, bool $bIncludeCss): string
    {
        $this->generateCodes();
        $sScript = '';
        if(($bIncludeCss))
        {
            $sScript .= $this->getCss() . "\n";
        }
        if(($bIncludeJs))
        {
            $sScript .= $this->getJs() . "\n";
        }

        if(!($sUrl = $this->xAssetManager->createJsFiles($this)))
        {
            return trim($sScript) . "\n\n" .
                $this->render('wrapper.js', [
                    'sScript' => trim($this->sJsScript) . "\n\n" .
                        trim($this->sJsInlineScript),
                ]);
        }
        return trim($sScript) . "\n\n" .
            trim($this->render('include.js', [
                'sUrl' => $sUrl,
            ])) . "\n\n" .
            trim($this->render('wrapper.js', [
                'sScript' => $this->sJsInlineScript,
            ]));
    }
}
