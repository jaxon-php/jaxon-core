<?php

namespace Jaxon;

use Jaxon\App\Session\SessionInterface;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Plugin\Response\DataBag\DataBagContext;
use Jaxon\Plugin\Response\JQuery\DomSelector;
use Jaxon\Request\Factory\ParameterFactory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Response\Response;
use Jaxon\Ui\View\ViewRenderer;
use Psr\Log\LoggerInterface;

class CallableClass
{
    /**
     * @var CallableClassHelper
     */
    protected $helper = null;

    /**
     * The Jaxon response returned by all classes methods
     *
     * @var Response
     */
    protected $response = null;

    /**
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    public function view(): ViewRenderer
    {
        return $this->helper->view;
    }

    /**
     * Get the session manager
     *
     * @return SessionInterface
     */
    public function session(): SessionInterface
    {
        return $this->helper->session;
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->helper->logger;
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
        // Find the class instance
        return $this->helper->registry->getCallableObject($sName)->getRegisteredObject();
    }

    /**
     * Get the request factory.
     *
     * @return RequestFactory
     */
    public function rq(): RequestFactory
    {
        return $this->helper->request;
    }

    /**
     * Get the parameter factory.
     *
     * @return ParameterFactory
     */
    public function pm(): ParameterFactory
    {
        return $this->helper->pm;
    }

    /**
     * Get the uploaded files
     *
     * @return array
     */
    public function files(): array
    {
        return $this->helper->upload->files();
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
