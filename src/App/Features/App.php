<?php

namespace Jaxon\App\Features;

use Jaxon\Contracts\Template\Renderer as TemplateRenderer;
use Jaxon\App\View\Facade as ViewFacade;

trait App
{
    /**
     * Set the Jaxon application options.
     *
     * @param Config        $xAppConfig        The config options
     *
     * @return void
     */
    private function jaxon()
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
}
