<?php

namespace Jaxon\App\Dialog\Library;

use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\LibraryInterface;
use Jaxon\Utils\Template\TemplateEngine;

use function array_merge;
use function is_bool;
use function is_numeric;
use function is_string;
use function json_encode;
use function rtrim;
use function str_repeat;
use function trim;

class DialogLibraryHelper
{
    /**
     * @var ConfigManager
     */
    protected $xConfigManager;

    /**
     * The Jaxon template engine
     *
     * @var TemplateEngine
     */
    protected $xTemplateEngine;

    /**
     * The name of the library
     *
     * @var string
     */
    protected $sName = '';

    /**
     * The URI where to get the library files from
     *
     * @var string
     */
    protected $sUri = '';

    /**
     * The subdir of the JS and CSS files in the CDN
     *
     * @var string
     */
    protected $sSubDir = '';

    /**
     * The default version of the plugin library
     *
     * @var string
     */
    protected $sVersion = '';

    /**
     * The constructor
     *
     * @param LibraryInterface $xDialogLibrary
     * @param ConfigManager $xConfigManager
     * @param TemplateEngine $xTemplateEngine
     */
    public function __construct(LibraryInterface $xDialogLibrary,
        ConfigManager $xConfigManager, TemplateEngine $xTemplateEngine)
    {
        $this->xConfigManager = $xConfigManager;
        $this->xTemplateEngine = $xTemplateEngine;

        // Set the library name
        $this->sName = $xDialogLibrary->getName();
        // Set the default URI.
        $sDefaultUri = $xConfigManager->getOption('dialogs.lib.uri', $xDialogLibrary->getUri());
        // Set the library URI.
        $this->sUri = rtrim($this->getOption('uri', $sDefaultUri), '/');
        // Set the subdir
        $this->sSubDir = trim($this->getOption('subdir', $xDialogLibrary->getSubDir()), '/');
        // Set the version number
        $this->sVersion = trim($this->getOption('version', $xDialogLibrary->getVersion()), '/');
    }

    /**
     * Get the value of a config option
     *
     * @param string $sOptionName The option name
     * @param mixed $xDefault The default value, to be returned if the option is not defined
     *
     * @return mixed
     */
    public function getOption(string $sOptionName, $xDefault = null)
    {
        $sOptionName = 'dialogs.' . $this->sName . '.' . $sOptionName;
        return $this->xConfigManager->getOption($sOptionName, $xDefault);
    }

    /**
     * Check the presence of a config option
     *
     * @param string $sOptionName The option name
     *
     * @return bool
     */
    public function hasOption(string $sOptionName): bool
    {
        $sOptionName = 'dialogs.' . $this->sName . '.' . $sOptionName;
        return $this->xConfigManager->hasOption($sOptionName);
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
        // The options names are relative to the plugin in Dialogs configuration
        return $this->xConfigManager->getOptionNames('dialogs.' . $this->sName . '.' . $sPrefix);
    }

    /**
     * Get the names of the options matching a given prefix
     *
     * @param string $sVarPrefix
     * @param string $sKeyPrefix
     * @param int $nSpaces
     *
     * @return string
     */
    public function getOptionScript(string $sVarPrefix, string $sKeyPrefix, int $nSpaces = 4): string
    {
        $aOptions = $this->getOptionNames($sKeyPrefix);
        $sSpaces = str_repeat(' ', $nSpaces);
        $sScript = '';
        foreach($aOptions as $sShortName => $sFullName)
        {
            $value = $this->xConfigManager->getOption($sFullName);
            if(is_string($value))
            {
                $value = "'$value'";
            }
            elseif(is_bool($value))
            {
                $value = ($value ? 'true' : 'false');
            }
            elseif(!is_numeric($value))
            {
                $value = json_encode($value);
            }
            $sScript .= "\n" . $sSpaces . $sVarPrefix . $sShortName . ' = ' . $value . ';';
        }
        return $sScript;
    }

    /**
     * Get the text of the "Yes" button for confirm dialog
     *
     * @return string
     */
    public function getQuestionTitle(): string
    {
        return $this->xConfigManager->getOption('dialogs.question.title', '');
    }

    /**
     * Get the javascript HTML header code
     *
     * @param string $sFile The javascript file name
     *
     * @return string
     */
    public function getJsCode(string $sFile): string
    {
        $sPath = ($this->sSubDir ? $this->sSubDir . '/' : '') . ($this->sVersion ? $this->sVersion . '/' : '');
        return '<script type="text/javascript" src="' . $this->sUri . '/' . $sPath . $sFile . '"></script>';
    }

    /**
     * Get the CSS HTML header code
     *
     * @param string $sFile The CSS file name
     *
     * @return string
     */
    public function getCssCode(string $sFile): string
    {
        $sPath = ($this->sSubDir ? $this->sSubDir . '/' : '') . ($this->sVersion ? $this->sVersion . '/' : '');
        return '<link rel="stylesheet" href="' . $this->sUri . '/' . $sPath . $sFile . '" />';
    }

    /**
     * Render a template
     *
     * @param string $sTemplate The name of template to be rendered
     * @param array $aVars The template vars
     *
     * @return string
     */
    public function render(string $sTemplate, array $aVars = []): string
    {
        // Is the library the default for alert messages?
        $isDefaultForMessage = ($this->sName == $this->xConfigManager->getOption('dialogs.default.message'));
        // Is the library the default for confirm questions?
        $isDefaultForQuestion = ($this->sName == $this->xConfigManager->getOption('dialogs.default.question'));
        $aLocalVars = [
            'yes' => $this->xConfigManager->getOption('dialogs.question.yes', 'Yes'),
            'no' =>  $this->xConfigManager->getOption('dialogs.question.no', 'No'),
            'defaultForMessage' => $isDefaultForMessage,
            'defaultForQuestion' => $isDefaultForQuestion
        ];
        return $this->xTemplateEngine->render('jaxon::dialogs::' . $sTemplate, array_merge($aLocalVars, $aVars));
    }
}
