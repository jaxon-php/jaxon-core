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
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Utils\Config\Reader as ConfigReader;

use Exception;

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
     * The class constructor
     *
     * @param Jaxon $jaxon
     * @param ResponseManager $xResponseManager
     * @param ConfigReader $xConfigReader
     */
    public function __construct(Jaxon $jaxon, ResponseManager $xResponseManager, ConfigReader $xConfigReader)
    {
        $this->jaxon = $jaxon;
        $this->xResponseManager = $xResponseManager;
        $this->xConfigReader = $xConfigReader;
    }

    /**
     * Read config options from a config file and setup the library
     *
     * @param string $sConfigFile The full path to the config file
     *
     * @return void
     * @throws Exception
     */
    public function setup($sConfigFile)
    {
        if(!file_exists($sConfigFile))
        {
            throw new Exception("Unable to find config file at $sConfigFile");
        }

        // Read the config options.
        $aOptions = $this->xConfigReader->read($sConfigFile);
        $aLibOptions = key_exists('lib', $aOptions) ? $aOptions['lib'] : [];
        $aAppOptions = key_exists('app', $aOptions) ? $aOptions['app'] : [];

        if(!is_array($aLibOptions) || !is_array($aAppOptions))
        {
            throw new Exception("Unexpected content in config file at $sConfigFile");
        }

        $this->bootstrap()
            ->lib($aLibOptions)
            ->app($aAppOptions)
            // ->uri($sUri)
            // ->js(!$isDebug, $sJsUrl, $sJsDir, !$isDebug)
            ->run();
    }

    /**
     * Get the HTTP response
     *
     * @param string    $code       The HTTP response code
     *
     * @return void
     */
    public function httpResponse($code = '200')
    {
        // Only if the response is not yet sent
        if(!$this->jaxon->getOption('core.response.send'))
        {
            // Set the HTTP response code
            http_response_code(intval($code));

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
