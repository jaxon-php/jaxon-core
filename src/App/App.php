<?php

/**
 * App.php
 *
 * Jaxon application
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\App\Traits\AjaxSendTrait;
use Jaxon\App\Traits\AppTrait;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;

use function file_exists;
use function http_response_code;
use function intval;
use function is_array;

class App implements AppInterface
{
    use AppTrait;
    use AjaxSendTrait;

    /**
     * The class constructor
     *
     * @param Container $xContainer
     */
    public function __construct(Container $xContainer)
    {
        $this->initApp($xContainer);
    }

    /**
     * Read config options from a config file and set up the library
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
            ->setup();
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function httpResponse(string $sCode = '200')
    {
        // Set the HTTP response code
        http_response_code(intval($sCode));

        // Send the response
        $this->sendResponse();
    }
}
