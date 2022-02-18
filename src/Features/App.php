<?php

namespace Jaxon\Features;

use Jaxon\App\Bootstrap;
use Jaxon\Contracts\Session;
use Jaxon\Plugin\Package;
use Jaxon\Request\Handler\Callback;
use Jaxon\Response\Response;
use Jaxon\Request\Factory\CallableClass\Request as CallableRequest;
use Jaxon\Utils\View\Renderer;
use Psr\Log\LoggerInterface;

use function jaxon;

trait App
{
    /**
     * Get the Jaxon application bootstrapper.
     *
     * @return Bootstrap
     */
    protected function bootstrap()
    {
        return jaxon()->di()->getBootstrap();
    }

    /**
     * Get the Jaxon response.
     *
     * @return Response
     */
    public function ajaxResponse()
    {
        return jaxon()->getResponse();
    }

    /**
     * Get an instance of a registered class
     *
     * @param string        $sClassName         The class name
     *
     * @return object|null
     */
    public function instance($sClassName)
    {
        return jaxon()->instance($sClassName);
    }

    /**
     * Get a request to a registered class
     *
     * @param string        $sClassName         The class name
     *
     * @return CallableRequest
     */
    public function request($sClassName)
    {
        return jaxon()->request($sClassName);
    }

    /**
     * Get a package instance
     *
     * @param string        $sClassName           The package class name
     *
     * @return Package
     */
    public function package($sClassName)
    {
        return jaxon()->package($sClassName);
    }

    /**
     * Get the request callback manager
     *
     * @return Callback
     */
    public function callback()
    {
        return jaxon()->callback();
    }

    /**
     * Determine if a call is a Jaxon request.
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        return jaxon()->canProcessRequest();
    }

    /**
     * Get the HTTP response
     *
     * @param string    $code       The HTTP response code
     *
     * @return mixed
     */
    abstract public function httpResponse($code = '200');

    /**
     * Process an incoming Jaxon request, and return the response.
     *
     * @return mixed
     */
    abstract public function processRequest();

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string  the javascript code
     */
    public function css()
    {
        return jaxon()->getCss();
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string  the javascript code
     */
    public function getCss()
    {
        return jaxon()->getCss();
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function js()
    {
        return jaxon()->getJs();
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function getJs()
    {
        return jaxon()->getJs();
    }

    /**
     * Get the javascript code to be sent to the browser.
     *
     * @return string  the javascript code
     */
    public function script($bIncludeJs = false, $bIncludeCss = false)
    {
        return jaxon()->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Get the javascript code to be sent to the browser.
     *
     * @return string  the javascript code
     */
    public function getScript($bIncludeJs = false, $bIncludeCss = false)
    {
        return jaxon()->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Get the view renderer
     *
     * @return Renderer
     */
    public function view()
    {
        return jaxon()->view();
    }

    /**
     * Get the session manager
     *
     * @return Session
     */
    public function session()
    {
        return jaxon()->session();
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger()
    {
        return jaxon()->logger();
    }

    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        jaxon()->setLogger($logger);
    }
}
