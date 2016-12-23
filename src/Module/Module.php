<?php

namespace Jaxon\Module;

use Jaxon\Config\Config;

class Module
{
    use Traits\Module;

    protected $configFile = '';

    /**
     * Initialise the Jaxon module.
     *
     * @return void
     */
    public function __construct()
    {}

    /**
     * Set the config file path.
     *
     * @return void
     */
    public function setConfigFile($configFile)
    {
        $this->configFile = $configFile;
    }

    /**
     * Set the module specific options for the Jaxon library.
     *
     * @return void
     */
    protected function setup()
    {
        // Read config file
        $this->appConfig = $jaxon->readConfigFile($this->configFile, 'lib', 'app');
        // Todo: check the mandatory options
    }

    /**
     * Set the module specific options for the Jaxon library.
     *
     * This method needs to set at least the Jaxon request URI.
     *
     * @return void
     */
    protected function check()
    {
        // Todo: check the mandatory options
    }

    /**
     * Return the view renderer.
     *
     * @return void
     */
    protected function view()
    {
        if($this->viewRenderer == null)
        {
            $this->viewRenderer = new View();
        }
        return $this->viewRenderer;
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
        $jaxon = jaxon();
        $jaxon->sendResponse();
    }
}
