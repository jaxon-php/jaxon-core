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

use Jaxon\Utils\Session\Manager as SessionManager;
use Exception;

class App
{
    use \Jaxon\Features\App;

    /**
     * Read config options from a config file and setup the library
     *
     * @param string        $sConfigFile        The full path to the config file
     *
     * @return Jaxon
     */
    public function setup($sConfigFile)
    {
        if(!file_exists($sConfigFile))
        {
            throw new Exception("Unable to find config file at $sConfigFile");
        }

        // Read the config options.
        $aOptions = jaxon()->config()->read($sConfigFile);
        $aLibOptions = key_exists('lib', $aOptions) ? $aOptions['lib'] : [];
        $aAppOptions = key_exists('app', $aOptions) ? $aOptions['app'] : [];

        if(!is_array($aLibOptions) || !is_array($aAppOptions))
        {
            throw new Exception("Unexpected content in config file at $sConfigFile");
        }

        // Set the session manager
        jaxon()->di()->setSessionManager(function () {
            return new SessionManager();
        });

        $this->bootstrap()
            ->lib($aLibOptions)
            ->app($aAppOptions)
            // ->uri($sUri)
            // ->js(!$isDebug, $sJsUrl, $sJsDir, !$isDebug)
            ->run(true);
    }
}
