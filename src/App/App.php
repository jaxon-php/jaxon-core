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
use Jaxon\Utils\Config\Exception\DataDepth;
use Jaxon\Utils\Config\Exception\YamlExtension;
use Jaxon\Utils\Config\Exception\FileAccess;
use Jaxon\Utils\Config\Exception\FileExtension;
use Jaxon\Utils\Config\Exception\FileContent;
use Jaxon\Utils\Translation\Translator;

use function intval;
use function file_exists;
use function is_array;
use function http_response_code;

class App
{
    use \Jaxon\Features\App;

    /**
     * @var Jaxon
     */
    private $jaxon;

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
     * @param string $sConfigFile The full path to the config file
     *
     * @return array
     * @throws SetupException
     */
    private function readConfig(string $sConfigFile): array
    {
        try
        {
            return $this->xConfigReader->read($sConfigFile);
        }
        catch(YamlExtension $e)
        {
            $sMessage = $this->xTranslator->trans('errors.yaml.install');
            throw new SetupException($sMessage);
        }
        catch(FileExtension $e)
        {
            $sMessage = $this->xTranslator->trans('errors.file.extension', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
        catch(FileAccess $e)
        {
            $sMessage = $this->xTranslator->trans('errors.file.access', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
        catch(FileContent $e)
        {
            $sMessage = $this->xTranslator->trans('errors.file.content', ['path' => $sConfigFile]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Read config options from a config file and setup the library
     *
     * @param string $sConfigFile The full path to the config file
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
        $aOptions = $this->readConfig($sConfigFile);
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
            ->run();
    }

    /**
     * Get the HTTP response
     *
     * @param string $sCode      The HTTP response code
     *
     * @return void
     */
    public function httpResponse(string $sCode = '200')
    {
        // Only if the response is not yet sent
        if(!$this->jaxon->getOption('core.response.send'))
        {
            // Set the HTTP response code
            http_response_code(intval($sCode));

            // Send the response
            $this->xResponseManager->sendOutput();

            if(($this->jaxon->getOption('core.process.exit')))
            {
                exit();
            }
        }
    }

    /**
     * Process an incoming Jaxon request, and return the response.
     *
     * @return void
     */
    public function processRequest()
    {
        $this->jaxon->processRequest();
    }
}
