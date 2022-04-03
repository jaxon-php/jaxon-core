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
use Jaxon\Config\ConfigManager;
use Jaxon\Utils\Translation\Translator;
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
     * @return Translator
     */
    protected function translator(): Translator
    {
        return $this->xTranslator;
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

        $this->bootstrap()
            ->lib($aLibOptions)
            ->app($aAppOptions)
            // ->uri($sUri)
            // ->js(!$bIsDebug, $sJsUrl, $sJsDir, !$bIsDebug)
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
