<?php

namespace Jaxon\Features;

trait App
{
    /**
     * Set the Jaxon application options.
     *
     * @param Config        $xAppConfig        The config options
     *
     * @return void
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
     * @param string        $sClass             The class name
     *
     * @return mixed
     */
    public function instance($sClassName)
    {
        return jaxon()->instance($sClassName);
    }

    /**
     * Get a request to a registered class
     *
     * @param string        $sClass             The class name
     *
     * @return \Jaxon\Request\Factory\CallableClass\Request
     */
    public function request($sClassName)
    {
        return jaxon()->request($sClassName);
    }

    /**
     * Get the request callback manager
     *
     * @return \Jaxon\Request\Handler\Callback
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
     * Process an incoming Jaxon request.
     *
     * @return void
     */
    public function processRequest()
    {
        return jaxon()->processRequest();
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
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function js()
    {
        return jaxon()->getJs();
    }

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
     * Get the view renderer
     *
     * @return Jaxon\Utils\View\Renderer
     */
    public function view()
    {
        return jaxon()->view();
    }

    /**
     * Get the session manager
     *
     * @return Jaxon\Contracts\Session
     */
    public function session()
    {
        return jaxon()->session();
    }
}
