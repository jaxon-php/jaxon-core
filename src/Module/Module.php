<?php

namespace Jaxon\Module;

use Jaxon\Config\Config;

class Module
{
    use Traits\Module;

    protected $view = null;

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
        $this->setConfigOptions($configFile, 'lib', 'app');
    }

    /**
     * Set the module specific options for the Jaxon library.
     *
     * @return void
     */
    protected function setup()
    {
        // Todo: check if the config file exists

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
        if($this->view == null)
        {
            $this->view = new View();
        }
        return $this->view;
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
