<?php

namespace Jaxon\App;

use Jaxon\Jaxon;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Package;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Response\ResponseInterface;
use Jaxon\Session\SessionInterface;
use Jaxon\Utils\Http\UriException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Closure;

use function trim;

trait AppTrait
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
     * Set the ajax endpoint URI
     *
     * @param string $sUri    The ajax endpoint URI
     *
     * @return void
     */
    public function uri(string $sUri)
    {
        $this->jaxon->setOption('core.request.uri', $sUri);
    }

    /**
     * Get the Jaxon response.
     *
     * @return ResponseInterface
     */
    public function ajaxResponse(): ResponseInterface
    {
        return $this->jaxon->getResponse();
    }

    /**
     * Get the configured character encoding
     *
     * @return string
     */
    public function getCharacterEncoding(): string
    {
        return trim($this->jaxon->getOption('core.encoding', ''));
    }

    /**
     * Get the content type of the HTTP response
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->jaxon->di()->getResponseManager()->getContentType();
    }

    /**
     * Get an instance of a registered class
     *
     * @param string $sClassName The class name
     *
     * @return object|null
     * @throws SetupException
     */
    public function instance(string $sClassName)
    {
        return $this->jaxon->instance($sClassName);
    }

    /**
     * Get a request to a registered class
     *
     * @param string $sClassName The class name
     *
     * @return RequestFactory|null
     * @throws SetupException
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
     * @return Package|null
     */
    public function package(string $sClassName): ?Package
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
     * @throws RequestException
     */
    public function processRequest()
    {
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
     * @return SessionInterface
     */
    public function session(): SessionInterface
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
        return $this->jaxon->di()->getLogger();
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
        $this->jaxon->di()->setLogger($logger);
    }

    /**
     * Set the container provided by the integrated framework
     *
     * @param ContainerInterface $xContainer    The container implementation
     *
     * @return void
     */
    public function setContainer(ContainerInterface $xContainer)
    {
        $this->jaxon->di()->setContainer($xContainer);
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
        $this->jaxon->di()->getViewRenderer()->addNamespace($sNamespace, $sDirectory, $sExtension, $sRenderer);
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
        $this->jaxon->di()->getViewRenderer()->addRenderer($sId, $xClosure);
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
