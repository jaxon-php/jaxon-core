<?php

namespace Jaxon\App;

use Jaxon\Config\Config;

use Jaxon\Features\Manager;
use Jaxon\Features\Event;
use Jaxon\Features\Validator;

use stdClass;
use Exception;
use Closure;

class App
{
    use Features\App;

    /**
     * The path to the config file
     *
     * @var string
     */
    // protected $sConfigFile;

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

        $aOptions = jaxon()->config()->read($sConfigFile);

        // Setup the config options.
        $aLibOptions = key_exists('lib', $aOptions) ? $aOptions['lib'] : [];
        $aAppOptions = key_exists('app', $aOptions) ? $aOptions['app'] : [];

        if(!is_array($aLibOptions) || !is_array($aAppOptions))
        {
            throw new Exception("Unexpected content in config file at $sConfigFile");
        }

        // Set the session manager
        // jaxon()->di()->setSessionManager(function () {
        //     return new Session\Manager();
        // });

        $xOptions = new Options\Options();
        $xOptions->lib($aLibOptions)->app($aAppOptions);
        // $xOptions->uri($sUri);
        // $xOptions->js()->export($bExtern)->minify($bMinify)->uri($sJsUri)->dir($sJsDir);
        $this->_bootstrap($xOptions);
    }

    /**
     * Wrap the Jaxon response into an HTTP response.
     *
     * @param  $code        The HTTP Response code
     *
     * @return HTTP Response
     */
    public function httpResponse($code = '200')
    {
        // Send HTTP Headers
        jaxon()->sendResponse();
    }
}
