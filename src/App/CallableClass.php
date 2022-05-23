<?php

namespace Jaxon\App;

use Jaxon\App\Session\SessionInterface;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Plugin\Response\DataBag\DataBagContext;
use Jaxon\Plugin\Response\JQuery\DomSelector;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\TargetInterface;
use Jaxon\Response\Response;
use Psr\Log\LoggerInterface;

class CallableClass
{
    /**
     * @var Response
     */
    protected $response = null;

    /**
     * @var CallableClassHelper
     */
    protected $xCallableClassHelper = null;

    /**
     * Get the Jaxon request target
     *
     * @return TargetInterface
     */
    protected function target(): TargetInterface
    {
        return $this->xCallableClassHelper->xTarget;
    }

    /**
     * Get an instance of a Jaxon class by name
     *
     * @param string $sName the class name
     *
     * @return object|null
     * @throws SetupException
     */
    public function cl(string $sName)
    {
        $xCallableClass = $this->xCallableClassHelper->xCallableRegistry->getCallableObject($sName);
        if($xCallableClass === null)
        {
            return null;
        }
        return $xCallableClass->getRegisteredObject();
    }

    /**
     * Get the request factory.
     *
     * @return RequestFactory
     */
    public function rq(): RequestFactory
    {
        return $this->xCallableClassHelper->xRequestFactory;
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->xCallableClassHelper->xLogger;
    }

    /**
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    public function view(): ViewRenderer
    {
        return $this->xCallableClassHelper->xViewRenderer;
    }

    /**
     * Get the session manager
     *
     * @return SessionInterface
     */
    public function session(): SessionInterface
    {
        return $this->xCallableClassHelper->xSessionManager;
    }

    /**
     * Get the uploaded files
     *
     * @return array
     */
    public function files(): array
    {
        return $this->xCallableClassHelper->xUploadHandler->files();
    }

    /**
     * Create a JQuery DomSelector, and link it to the response attribute.
     *
     * @param string $sPath    The jQuery selector path
     * @param string $sContext    A context associated to the selector
     *
     * @return DomSelector
     */
    public function jq(string $sPath = '', string $sContext = ''): DomSelector
    {
        return $this->response->jq($sPath, $sContext);
    }

    /**
     * Get a data bag.
     *
     * @param string  $sName
     *
     * @return DataBagContext
     */
    public function bag(string $sName): DataBagContext
    {
        return $this->response->bag($sName);
    }
}
