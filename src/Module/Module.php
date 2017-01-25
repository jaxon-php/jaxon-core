<?php

namespace Jaxon\Module;

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
     * Read and set Jaxon options from a config file
     *
     * @return array
     */
    protected function readConfig()
    {
        $sExt = pathinfo($this->configFile, PATHINFO_EXTENSION);
        switch($sExt)
        {
        case 'php':
            return \Jaxon\Config\Php::read($this->configFile, 'lib', 'app');
            break;
        case 'yaml':
        case 'yml':
            return \Jaxon\Config\Yaml::read($this->configFile, 'lib', 'app');
            break;
        case 'json':
            return \Jaxon\Config\Json::read($this->configFile, 'lib', 'app');
            break;
        default:
            throw new \Jaxon\Exception\Config\File('access', $this->configFile);
            break;
        }
    }

    /**
     * Set the config file path.
     *
     * @return void
     */
    public function config($configFile)
    {
        $this->configFile = $configFile;
    }

    /**
     * Set the module specific options for the Jaxon library.
     *
     * @return void
     */
    protected function jaxonSetup()
    {
        // Read config file
        $this->appConfig = $this->readConfig();
    }

    /**
     * Set the module specific options for the Jaxon library.
     *
     * This method needs to set at least the Jaxon request URI.
     *
     * @return void
     */
    protected function jaxonCheck()
    {
        // Check the mandatory options
        // Jaxon library settings
        /*$aMandatoryOptions = ['js.app.extern', 'js.app.minify', 'js.app.uri', 'js.app.dir'];
        foreach($aMandatoryOptions as $sOption)
        {
            if(!$jaxon->hasOption($sOption))
            {
                throw new \Jaxon\Exception\Config\Data('missing', 'lib:' . $sOption);
            }
        }*/
        // Jaxon application settings
        $aMandatoryOptions = ['controllers.directory', 'controllers.namespace'];
        foreach($aMandatoryOptions as $sOption)
        {
            if(!$this->appConfig->hasOption($sOption))
            {
                throw new \Jaxon\Exception\Config\Data('missing', 'app:' . $sOption);
            }
        }
    }

    /**
     * Return the view renderer.
     *
     * @return void
     */
    protected function jaxonView()
    {
        if($this->jaxonViewRenderer == null)
        {
            $this->jaxonViewRenderer = new View();
        }
        return $this->jaxonViewRenderer;
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
