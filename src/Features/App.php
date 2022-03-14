<?php

namespace Jaxon\Features;

use Jaxon\Jaxon;
use Jaxon\App\Bootstrap;
use Jaxon\Contracts\Session;
use Jaxon\Plugin\Package;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Response\AbstractResponse;
use Jaxon\Ui\View\ViewRenderer;
use Jaxon\Utils\Http\UriException;
use Jaxon\Exception\SetupException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Closure;

trait App
{
    /**
     * @var Jaxon
     */
    protected $jaxon;

    /**
     * Get the Jaxon application bootstrapper.
     *
     * @return Bootstrap
     */
    protected function bootstrap(): Bootstrap
    {
        return $this->jaxon->di()->getBootstrap();
    }

    /**
     * Get the Jaxon response.
     *
     * @return AbstractResponse
     */
    public function ajaxResponse(): AbstractResponse
    {
        return $this->jaxon->getResponse();
    }

    /**
     * Get an instance of a registered class
     *
     * @param string $sClassName    The class name
     *
     * @return object|null
     */
    public function instance(string $sClassName)
    {
        return $this->jaxon->instance($sClassName);
    }

    /**
     * Get a request to a registered class
     *
     * @param string $sClassName    The class name
     *
     * @return RequestFactory|null
     */
    public function request(string $sClassName): ?RequestFactory
    {
        return $this->jaxon->request($sClassName);
    }

    /**
     * Get a package instance
     *
     * @param string $sClassName    The package class name
     *
     * @return Package
     */
    public function package(string $sClassName): Package
    {
        return $this->jaxon->package($sClassName);
    }

    /**
     * Get the request callback manager
     *
     * @return CallbackManager
     */
    public function callback(): CallbackManager
    {
        return $this->jaxon->callback();
    }

    /**
     * Check if a call is a Jaxon request.
     *
     * @return bool
     */
    public function canProcessRequest(): bool
    {
        return $this->jaxon->canProcessRequest();
    }

    /**
     * Get the HTTP response
     *
     * @param string $sCode    The HTTP response code
     *
     * @return mixed
     */
    abstract public function httpResponse(string $sCode = '200');

    /**
     * Process an incoming Jaxon request, and return the response.
     *
     * @return mixed
     * @throws SetupException
     */
    public function processRequest()
    {
        // Prevent the Jaxon library from sending the response or exiting
        $this->jaxon->config()->setOption('core.response.send', false);
        $this->jaxon->config()->setOption('core.process.exit', false);

        // Process the jaxon request
        $this->jaxon->processRequest();

        // Return the response to the request
        return $this->httpResponse();
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string  the javascript code
     */
    public function css(): string
    {
        return $this->jaxon->getCss();
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page.
     *
     * @return string  the javascript code
     */
    public function getCss(): string
    {
        return $this->jaxon->getCss();
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function js(): string
    {
        return $this->jaxon->getJs();
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page.
     *
     * @return string  the javascript code
     */
    public function getJs(): string
    {
        return $this->jaxon->getJs();
    }

    /**
     * Get the javascript code to be sent to the browser.
     *
     * @return string  the javascript code
     * @throws UriException
     */
    public function script(bool $bIncludeJs = false, bool $bIncludeCss = false): string
    {
        return $this->jaxon->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Get the javascript code to be sent to the browser.
     *
     * @return string  the javascript code
     * @throws UriException
     */
    public function getScript(bool $bIncludeJs = false, bool $bIncludeCss = false): string
    {
        return $this->jaxon->getScript($bIncludeJs, $bIncludeCss);
    }

    /**
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    public function view(): ViewRenderer
    {
        return $this->jaxon->view();
    }

    /**
     * Get the session manager
     *
     * @return Session
     */
    public function session(): Session
    {
        return $this->jaxon->session();
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->jaxon->logger();
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
        $this->jaxon->setLogger($logger);
    }

    /**
     * Set the container provided by the integrated framework
     *
     * @param ContainerInterface $xContainer    The container implementation
     *
     * @return void
     */
    public function setAppContainer(ContainerInterface $xContainer)
    {
        $this->jaxon->di()->setAppContainer($xContainer);
    }

    /**
     * Add a view namespace, and set the corresponding renderer.
     *
     * @param string $sNamespace    The namespace name
     * @param string $sDirectory    The namespace directory
     * @param string $sExtension    The extension to append to template names
     * @param string $sRenderer    The corresponding renderer name
     *
     * @return void
     */
    public function addViewNamespace(string $sNamespace, string $sDirectory, string $sExtension, string $sRenderer)
    {
        $this->jaxon->di()->getViewManager()->addNamespace($sNamespace, $sDirectory, $sExtension, $sRenderer);
    }

    /**
     * Add a view renderer with an id
     *
     * @param string $sId    The unique identifier of the view renderer
     * @param Closure $xClosure    A closure to create the view instance
     *
     * @return void
     */
    public function addViewRenderer(string $sId, Closure $xClosure)
    {
        $this->jaxon->di()->getViewManager()->addRenderer($sId, $xClosure);
    }

    /**
     * Set the session manager
     *
     * @param Closure $xClosure    A closure to create the session manager instance
     *
     * @return void
     */
    public function setSessionManager(Closure $xClosure)
    {
        $this->jaxon->di()->setSessionManager($xClosure);
    }
}
