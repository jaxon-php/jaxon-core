<?php

namespace Jaxon\Module\Traits;

use Jaxon\Jaxon;
use Jaxon\Module\Controller;
use Jaxon\Response\Response;
use Jaxon\Utils\Traits\Event;

use stdClass;

trait Module
{
    use Event;

    protected $jaxonSetupCalled = false;

    protected $jaxonPreCallback = null;
    protected $jaxonPostCallback = null;
    protected $jaxonInitCallback = null;

    // Requested class and method
    private $jaxonRequestObject = null;
    private $jaxonRequestMethod = null;

    protected $appConfig = null;
    protected $jaxonResponse = null;
    protected $jaxonViewRenderer = null;
    protected $jaxonControllerClass = '\\Jaxon\\Module\\Controller';

    // Library and application options
    private $jaxonLibOptions = null;
    private $jaxonAppOptions = null;

    /**
     * Set the module specific options for the Jaxon library.
     *
     * @return void
     */
    abstract protected function jaxonSetup();

    /**
     * Set the module specific options for the Jaxon library.
     *
     * @return void
     */
    abstract protected function jaxonCheck();

    /**
     * Return the view renderer.
     *
     * @return void
     */
    abstract protected function jaxonView();

    /**
     * Wrap the Jaxon response into an HTTP response and send it back to the browser.
     *
     * @param  $code        The HTTP Response code
     *
     * @return HTTP Response
     */
    abstract public function httpResponse($code = '200');

    /**
     * Get the Jaxon response.
     *
     * @return HTTP Response
     */
    public function ajaxResponse()
    {
        return $this->jaxonResponse;
    }

    /**
     * Set the Jaxon library default options.
     *
     * @return void
     */
    protected function setLibraryOptions($bExtern, $bMinify, $sJsUri, $sJsDir)
    {
        if(!$this->jaxonLibOptions)
        {
            $this->jaxonLibOptions = new stdClass();
        }
        $this->jaxonLibOptions->bExtern = $bExtern;
        $this->jaxonLibOptions->bMinify = $bMinify;
        $this->jaxonLibOptions->sJsUri = $sJsUri;
        $this->jaxonLibOptions->sJsDir = $sJsDir;
    }

    /**
     * Set the Jaxon application default options.
     *
     * @return void
     */
    protected function setApplicationOptions($sDirectory, $sNamespace)
    {
        if(!$this->jaxonAppOptions)
        {
            $this->jaxonAppOptions = new stdClass();
        }
        $this->jaxonAppOptions->sDirectory = $sDirectory;
        $this->jaxonAppOptions->sNamespace = $sNamespace;
    }

    /**
     * Set the Jaxon controller base class name.
     *
     * @return void
     */
    protected function setControllerClass($controllerClass)
    {
        $this->jaxonControllerClass = $controllerClass;
    }

    /**
     * Wraps the module/package/bundle setup method.
     *
     * @return void
     */
    private function _jaxonSetup()
    {
        if(($this->jaxonSetupCalled))
        {
            return;
        }

        // Event before setting up the module
        $this->triggerEvent('pre.setup');

        // Set the module/package/bundle specific specific options
        $this->jaxonSetup();

        // Event after the module has read the config
        $this->triggerEvent('post.config');

        $jaxon = jaxon();
        // Use the Composer autoloader
        $jaxon->useComposerAutoloader();
        // Create the Jaxon response
        $this->jaxonResponse = new Response();

        if(($this->jaxonLibOptions) && ($this->jaxonAppOptions))
        {
            // Jaxon library settings
            if(!$jaxon->hasOption('js.app.extern'))
            {
                $jaxon->setOption('js.app.extern', $this->jaxonLibOptions->bExtern);
            }
            if(!$jaxon->hasOption('js.app.minify'))
            {
                $jaxon->setOption('js.app.minify', $this->jaxonLibOptions->bMinify);
            }
            if(!$jaxon->hasOption('js.app.uri'))
            {
                $jaxon->setOption('js.app.uri', $this->jaxonLibOptions->sJsUri);
            }
            if(!$jaxon->hasOption('js.app.dir'))
            {
                $jaxon->setOption('js.app.dir', $this->jaxonLibOptions->sJsDir);
            }
    
            if(!$this->appConfig->hasOption('controllers.directory'))
            {
                $this->appConfig->setOption('controllers.directory', $this->jaxonAppOptions->sDirectory);
            }
            if(!$this->appConfig->hasOption('controllers.namespace'))
            {
                $this->appConfig->setOption('controllers.namespace', $this->jaxonAppOptions->sNamespace);
            }
    
            // Set the request URI
            if(!$jaxon->hasOption('core.request.uri'))
            {
                $jaxon->setOption('core.request.uri', 'jaxon');
            }
        }

        // Event before checking the module
        $this->triggerEvent('pre.check');

        $this->jaxonCheck();

        // Jaxon application settings
        // Register the default Jaxon class directory
        $directory = $this->appConfig->getOption('controllers.directory');
        $namespace = $this->appConfig->getOption('controllers.namespace');
        $separator = $this->appConfig->getOption('controllers.separator', '.');
        $protected = $this->appConfig->getOption('controllers.protected', array());
        // The public methods of the Controller base class must not be exported to javascript
        $controllerClass = new \ReflectionClass($this->jaxonControllerClass);
        foreach ($controllerClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $protected[] = $xMethod->getShortName();
        }
        $jaxon->addClassDir($directory, $namespace, $separator, $protected);

        // Event after setting up the module
        $this->triggerEvent('post.setup');

        $this->jaxonSetupCalled = true;
    }

    /**
     * Register the Jaxon classes.
     *
     * @return void
     */
    public function register()
    {
        $this->_jaxonSetup();
        jaxon()->registerClasses();
    }

    /**
     * Register a specified Jaxon class.
     *
     * @return void
     */
    public function registerClass($sClassName)
    {
        $this->_jaxonSetup();
        jaxon()->registerClass($sClassName);
    }

    /**
     * Get the javascript code to be sent to the browser.
     *
     * @return string  the javascript code
     */
    public function script($bIncludeJs = false, $bIncludeCss = false)
    {
        $this->_jaxonSetup();
        return jaxon()->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function js()
    {
        $this->_jaxonSetup();
        return jaxon()->getJs();
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string  the javascript code
     */
    public function css()
    {
        $this->_jaxonSetup();
        return jaxon()->getCss();
    }

    /**
     * Set the init callback, used to initialise controllers.
     *
     * @param  callable  $callable the callback function
     * @return void
     */
    public function setInitCallback($callable)
    {
        $this->jaxonInitCallback = $callable;
    }

    /**
     * Set the pre-request processing callback.
     *
     * @param  callable  $callable the callback function
     * @return void
     */
    public function setPreCallback($callable)
    {
        $this->jaxonPreCallback = $callable;
    }

    /**
     * Set the post-request processing callback.
     *
     * @param  callable  $callable the callback function
     * 
     * @return void
     */
    public function setPostCallback($callable)
    {
        $this->jaxonPostCallback = $callable;
    }

    /**
     * Initialise a controller.
     *
     * @return void
     */
    protected function initController(Controller $controller)
    {
        // Return if the controller has already been initialised.
        if(!($controller) || ($controller->module))
        {
            return;
        }
        // Init the controller
        $controller->module = $this;
        $controller->response = $this->jaxonResponse;
        if(($this->jaxonInitCallback))
        {
            $cb = $this->jaxonInitCallback;
            $cb($controller);
        }
        $controller->init();
        // The default view is used only if there is none already set
        if(!$controller->view)
        {
            $controller->view = $this->jaxonView();
        }
    }

    /**
     * Get a controller instance.
     *
     * @param  string  $classname the controller class name
     * 
     * @return object  The registered instance of the controller
     */
    public function controller($classname)
    {
        $this->_jaxonSetup();
        $controller = jaxon()->registerClass($classname, true);
        if(!$controller)
        {
            return null;
        }
        $this->initController($controller);
        return $controller;
    }

    /**
     * This is the pre-request processing callback passed to the Jaxon library.
     *
     * @param  boolean  &$bEndRequest if set to true, the request processing is interrupted.
     * 
     * @return object  the Jaxon response
     */
    public function preProcess(&$bEndRequest)
    {
        // Validate the inputs
        $class = $_POST['jxncls'];
        $method = $_POST['jxnmthd'];
        $jaxon = jaxon();
        if(!$jaxon->validateClass($class) || !$jaxon->validateMethod($method))
        {
            // End the request processing if the input data are not valid.
            // Todo: write an error message in the response
            $bEndRequest = true;
            return $this->jaxonResponse;
        }
        // Instanciate the controller. This will include the required file.
        $this->jaxonRequestObject = $this->controller($class);
        $this->jaxonRequestMethod = $method;
        if(!$this->jaxonRequestObject)
        {
            // End the request processing if a controller cannot be found.
            // Todo: write an error message in the response
            $bEndRequest = true;
            return $this->jaxonResponse;
        }

        // Call the user defined callback
        if(($this->jaxonPreCallback))
        {
            $cb = $this->jaxonPreCallback;
            $cb($this->jaxonRequestObject, $method, $bEndRequest);
        }
        return $this->jaxonResponse;
    }

    /**
     * This is the post-request processing callback passed to the Jaxon library.
     *
     * @return object  the Jaxon response
     */
    public function postProcess()
    {
        if(($this->jaxonPostCallback))
        {
            $cb = $this->jaxonPostCallback;
            $cb($this->jaxonRequestObject, $this->jaxonRequestMethod);
        }
        return $this->jaxonResponse;
    }

    /**
     * Check if the current request is a Jaxon request.
     *
     * @return boolean  True if the request is Jaxon, false otherwise.
     */
    public function canProcessRequest()
    {
        $this->_jaxonSetup();
        return jaxon()->canProcessRequest();
    }

    /**
     * Process the current Jaxon request.
     *
     * @return void
     */
    public function processRequest()
    {
        $this->_jaxonSetup();
        // Process Jaxon Request
        $jaxon = jaxon();
        $jaxon->register(Jaxon::PROCESSING_EVENT, Jaxon::PROCESSING_EVENT_BEFORE, array($this, 'preProcess'));
        $jaxon->register(Jaxon::PROCESSING_EVENT, Jaxon::PROCESSING_EVENT_AFTER, array($this, 'postProcess'));
        if($jaxon->canProcessRequest())
        {
            // Traiter la requete
            $jaxon->processRequest();
        }
    }
}
