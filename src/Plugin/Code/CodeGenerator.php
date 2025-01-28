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

use Jaxon\Di\Container;
use Jaxon\Plugin\AbstractPlugin;
use Jaxon\Plugin\CodeGeneratorInterface;
use Jaxon\Utils\Http\UriException;
use Jaxon\Utils\Template\TemplateEngine;

use function array_merge;
use function array_reduce;
use function count;
use function implode;
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
     * @var array
     */
    protected $aCss = [];

    /**
     * @var array
     */
    protected $aJs = [];

    /**
     * @var array
     */
    protected $aCodeJs = [];

    /**
     * @var array
     */
    protected $aCodeJsBefore = [];

    /**
     * @var array
     */
    protected $aCodeJsAfter = [];

    /**
     * @var array
     */
    protected $aCodeJsFiles = [];

    /**
     * @var string
     */
    protected $bGenerated = false;

    /**
     * The constructor
     *
     * @param string $sVersion
     * @param Container $di
     * @param TemplateEngine $xTemplateEngine
     */
    public function __construct(private string $sVersion, private Container $di,
        private TemplateEngine $xTemplateEngine)
    {
        // The Jaxon library config is on top.
        $this->addCodeGenerator(ConfigScriptGenerator::class, 0);
        // The ready script comes after.
        $this->addCodeGenerator(ReadyScriptGenerator::class, 200);
    }

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
     * @param string $sClassName
     *
     * @return CodeGeneratorInterface
     */
    private function getCodeGenerator(string $sClassName): CodeGeneratorInterface
    {
        return $this->di->g($sClassName);
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
     * @param CodeGeneratorInterface $xGenerator
     *
     * @return void
     */
    private function generatePluginCodes(CodeGeneratorInterface $xGenerator)
    {
        if(!is_subclass_of($xGenerator, AbstractPlugin::class) ||
            $this->xAssetManager->shallIncludeAssets($xGenerator))
        {
            // HTML tags for CSS
            if(($sCss = trim($xGenerator->getCss(), " \n")) !== '')
            {
                $this->aCss[] = $sCss;
            }
            // HTML tags for js
            if(($sJs = trim($xGenerator->getJs(), " \n")) !== '')
            {
                $this->aJs[] = $sJs;
            }
        }

        // Additional js codes
        if(($xJsCode = $xGenerator->getJsCode()) !== null)
        {
            if(($sJs = trim($xJsCode->sJs, " \n")) !== '')
            {
                $this->aCodeJs[] = $sJs;
            }
            if(($sJsBefore = trim($xJsCode->sJsBefore, " \n")) !== '')
            {
                $this->aCodeJsBefore[] = $sJsBefore;
            }
            if(($sJsAfter = trim($xJsCode->sJsAfter, " \n")) !== '')
            {
                $this->aCodeJsAfter[] = $sJsAfter;
            }
            $this->aCodeJsFiles = array_merge($this->aCodeJsFiles, $xJsCode->aFiles);
        }
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

        $this->xAssetManager = $this->di->getAssetManager();
        $this->sJsOptions = $this->xAssetManager->getJsOptions();

        // Sort the code generators by ascending priority
        ksort($this->aCodeGenerators);

        foreach($this->aCodeGenerators as $sClassName)
        {
            $this->generatePluginCodes($this->getCodeGenerator($sClassName));
        }

        // Load the Jaxon lib js files, after the other libs js files.
        $this->aJs[] = trim($this->render('includes.js', [
            'aUrls' => $this->xAssetManager->getJsLibFiles(),
        ]));

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
        return implode("\n\n", $this->aCss);
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
        return implode("\n\n", $this->aJs);
    }

    /**
     * Get the Javascript code
     *
     * @return string
     */
    public function getJsScript(): string
    {
        foreach($this->aCodeGenerators as $sClassName)
        {
            /** @var CodeGeneratorInterface */
            $xGenerator = $this->di->g($sClassName);
            // Javascript code
            if(($sJsScript = trim($xGenerator->getScript(), " \n")) !== '')
            {
                $aJsScript[] = $sJsScript;
            }
        }
        return implode("\n\n", $aJsScript);
    }

    /**
     * @param bool $bIncludeJs Also get the JS files
     * @param bool $bIncludeCss Also get the CSS files
     *
     * @return array<string>
     */
    private function renderCodes(bool $bIncludeJs, bool $bIncludeCss): array
    {
        $aCodes = [];
        if($bIncludeCss)
        {
            $aCodes[] = $this->getCss();
        }
        if($bIncludeJs)
        {
            $aCodes[] = $this->getJs();
        }

        $sUrl = !$this->xAssetManager->shallCreateJsFiles() ? '' :
            $this->xAssetManager->createJsFiles($this);
        // Wrap the js code into the corresponding HTML tag.
        $aCodes[] = $sUrl !== '' ?
            $this->render('include.js', ['sUrl' => $sUrl]) :
            $this->render('wrapper.js', ['sScript' => $this->getJsScript()]);

        // Wrap the js codes into HTML tags.
        if(count($this->aCodeJsBefore) > 0)
        {
            $sScript = implode("\n\n", $this->aCodeJsBefore);
            $aCodes[] = $this->render('wrapper.js', ['sScript' => $sScript]);
        }
        if(count($this->aCodeJs) > 0)
        {
            $sScript = implode("\n\n", $this->aCodeJs);
            $aCodes[] = $this->render('wrapper.js', ['sScript' => $sScript]);
        }
        if(count($this->aCodeJsFiles) > 0)
        {
            $aCodes[] = $this->render('includes.js', ['aUrls' => $this->aCodeJsFiles]);
        }
        if(count($this->aCodeJsAfter) > 0)
        {
            $sScript = implode("\n\n", $this->aCodeJsAfter);
            $aCodes[] = $this->render('wrapper.js', ['sScript' => $sScript]);
        }
        return $aCodes;
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
        $aCodes = $this->renderCodes($bIncludeJs, $bIncludeCss);
        return implode("\n\n", $aCodes);
    }
}
