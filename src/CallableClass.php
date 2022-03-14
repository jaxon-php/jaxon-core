<?php

namespace Jaxon;

use Jaxon\Contracts\Session;
use Jaxon\Request\Factory\ParameterFactory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Response\Plugin\DataBag\Context as DataBagContext;
use Jaxon\Response\Plugin\JQuery\DomSelector;
use Jaxon\Response\Response;
use Jaxon\Ui\View\ViewRenderer;
use Jaxon\Exception\SetupException;

use Psr\Log\LoggerInterface;

class CallableClass
{
    /**
     * @var Jaxon
     */
    protected $jaxon = null;

    /**
     * The Jaxon response returned by all classes methods
     *
     * @var Response
     */
    protected $response = null;

    /**
     * The name of the registered class
     *
     * @var string
     */
    protected $_class = '';

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
     * Get the request factory.
     *
     * @return RequestFactory
     */
    public function rq(): RequestFactory
    {
        return $this->jaxon->factory()->request($this->_class);
    }

    /**
     * Get the parameter factory.
     *
     * @return ParameterFactory
     */
    public function pm(): ParameterFactory
    {
        return $this->jaxon->factory()->parameter();
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
        return $this->response->plugin('jquery')->selector($sPath, $sContext);
    }

    /**
     * Get an instance of a Jaxon class by name
     *
     * @param string $sName the class name
     *
     * @return object
     * @throws SetupException
     */
    public function cl(string $sName)
    {
        // Find the class instance
        return $this->jaxon->instance($sName);
    }

    /**
     * Get the uploaded files
     *
     * @return array
     */
    public function files(): array
    {
        return $this->jaxon->upload()->files();
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
        return $this->response->plugin('bags')->bag($sName);
    }
}
