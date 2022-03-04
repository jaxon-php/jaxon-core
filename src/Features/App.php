<?php

namespace Jaxon\Features;

use Jaxon\App\Bootstrap;
use Jaxon\Contracts\Session;
use Jaxon\Plugin\Package;
use Jaxon\Request\Factory\CallableClass\Request as CallableRequest;
use Jaxon\Request\Handler\Callback;
use Jaxon\Response\Response;
use Jaxon\Ui\View\Renderer;
use Psr\Log\LoggerInterface;

use function jaxon;

trait App
{
    /**
     * Get the Jaxon application bootstrapper.
     *
     * @return Bootstrap
     */
    protected function bootstrap(): Bootstrap
    {
        return jaxon()->di()->getBootstrap();
    }

    /**
     * Get the Jaxon response.
     *
     * @return Response
     */
    public function ajaxResponse(): Response
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
    public function instance(string $sClassName)
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
    public function request(string $sClassName)
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
    public function package(string $sClassName): Package
    {
        return jaxon()->package($sClassName);
    }

    /**
     * Get the request callback manager
     *
     * @return Callback
     */
    public function callback(): Callback
    {
        return jaxon()->callback();
    }

    /**
     * Check if a call is a Jaxon request.
     *
     * @return bool
     */
    public function canProcessRequest(): bool
    {
        return jaxon()->canProcessRequest();
    }

    /**
     * Get the HTTP response
     *
     * @param string $sCode      The HTTP response code
     *
     * @return mixed
     */
    abstract public function httpResponse(string $sCode = '200');

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
    public function css(): string
    {
        return jaxon()->getCss();
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string  the javascript code
     */
    public function getCss(): string
    {
        return jaxon()->getCss();
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function js(): string
    {
        return jaxon()->getJs();
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function getJs(): string
    {
        return jaxon()->getJs();
    }

    /**
     * Get the javascript code to be sent to the browser.
     *
     * @return string  the javascript code
     */
    public function script(bool $bIncludeJs = false, bool $bIncludeCss = false): string
    {
        return jaxon()->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Get the javascript code to be sent to the browser.
     *
     * @return string  the javascript code
     */
    public function getScript(bool $bIncludeJs = false, bool $bIncludeCss = false): string
    {
        return jaxon()->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Get the view renderer
     *
     * @return Renderer
     */
    public function view(): Renderer
    {
        return jaxon()->view();
    }

    /**
     * Get the session manager
     *
     * @return Session
     */
    public function session(): Session
    {
        return jaxon()->session();
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
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
