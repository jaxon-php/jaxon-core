<?php

/**
 * App.php - Jaxon application
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App;

use Jaxon\Jaxon;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Exception\SetupException;

use function file_exists;
use function http_response_code;
use function intval;
use function is_array;

class App
{
    use AppTrait;

    /**
     * @var ConfigManager
     */
    private $xConfigManager;

    /**
     * @var Translator
     */
    private $xTranslator;

    /**
     * The class constructor
     *
     * @param Jaxon $jaxon
     * @param ConfigManager $xConfigManager
     * @param Translator $xTranslator
     */
    public function __construct(Jaxon $jaxon, ConfigManager $xConfigManager, Translator $xTranslator)
    {
        $this->jaxon = $jaxon;
        $this->xConfigManager = $xConfigManager;
        $this->xTranslator = $xTranslator;
    }

    /**
     * Set the javascript asset
     *
     * @param bool $bExport    Whether to export the js code in a file
     * @param bool $bMinify    Whether to minify the exported js file
     * @param string $sUri    The URI to access the js file
     * @param string $sDir    The directory where to create the js file
     *
     * @return App
     */
    public function asset(bool $bExport, bool $bMinify, string $sUri = '', string $sDir = ''): App
    {
        $this->bootstrap()->asset($bExport, $bMinify, $sUri, $sDir);
        return $this;
    }

    /**
     * Read config options from a config file and setup the library
     *
     * @param string $sConfigFile    The full path to the config file
     *
     * @return void
     * @throws SetupException
     */
    public function setup(string $sConfigFile)
    {
        if(!file_exists($sConfigFile))
        {
            $sMessage = $this->xTranslator->trans('errors.file.access', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }

        // Read the config options.
        $aOptions = $this->xConfigManager->read($sConfigFile);
        $aLibOptions = $aOptions['lib'] ?? [];
        $aAppOptions = $aOptions['app'] ?? [];
        if(!is_array($aLibOptions) || !is_array($aAppOptions))
        {
            $sMessage = $this->xTranslator->trans('errors.file.content', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
        // The bootstrap set this to false. It needs to be changed.
        if(!isset($aLibOptions['core']['response']['send']))
        {
            $aLibOptions['core']['response']['send'] = true;
        }

        $this->bootstrap()
            ->lib($aLibOptions)
            ->app($aAppOptions)
            // ->asset(!$bIsDebug, !$bIsDebug, $sJsUrl, $sJsDir)
            ->setup();
    }

    /**
     * @inheritDoc
     */
    public function httpResponse(string $sCode = '200')
    {
        // Set the HTTP response code
        http_response_code(intval($sCode));
        // Send the response
        $this->jaxon->sendResponse();
    }
}
