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
use Jaxon\Exception\SetupException;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Utils\Config\Reader as ConfigReader;
use Jaxon\Utils\Translation\Translator;

use function intval;
use function file_exists;
use function is_array;
use function http_response_code;

class App
{
    use \Jaxon\Features\App;

    /**
     * @var ResponseManager
     */
    private $xResponseManager;

    /**
     * @var ConfigReader
     */
    private $xConfigReader;

    /**
     * @var Translator
     */
    private $xTranslator;

    /**
     * The class constructor
     *
     * @param Jaxon $jaxon
     * @param ResponseManager $xResponseManager
     * @param ConfigReader $xConfigReader
     * @param Translator $xTranslator
     */
    public function __construct(Jaxon $jaxon, ResponseManager $xResponseManager,
        ConfigReader $xConfigReader, Translator $xTranslator)
    {
        $this->jaxon = $jaxon;
        $this->xResponseManager = $xResponseManager;
        $this->xConfigReader = $xConfigReader;
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
        $aOptions = $this->jaxon->readConfig($sConfigFile);
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
        $this->xResponseManager->sendOutput();
    }
}
