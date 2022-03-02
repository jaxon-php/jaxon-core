<?php

namespace Jaxon;

use Jaxon\Contracts\Session;
use Jaxon\Request\Factory\CallableClass\Request;
use Jaxon\Response\Plugin\DataBag\Context as DataBagContext;
use Jaxon\Response\Plugin\JQuery\Dom\Element as DomElement;
use Jaxon\Response\Response;
use Jaxon\Ui\View\Renderer;
use Psr\Log\LoggerInterface;

class CallableClass
{
    /**
     * @var Jaxon
     */
    protected $jaxon = null;

    /**
     * The request factory DI key
     *
     * @var string
     */
    protected $sRequest = '';

    /**
     * The Jaxon response returned by all classes methods
     *
     * @var Response
     */
    protected $response = null;

    /**
     * Get the view renderer
     *
     * @return Renderer
     */
    public function view()
    {
        return $this->jaxon->view();
    }

    /**
     * Get the session manager
     *
     * @return Session
     */
    public function session()
    {
        return $this->jaxon->session();
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger()
    {
        return $this->jaxon->logger();
    }

    /**
     * Get the request factory.
     *
     * @return Request
     */
    public function rq()
    {
        return $this->jaxon->di()->g($this->sRequest);
    }

    /**
     * Create a JQuery Element with a given selector, and link it to the response attribute.
     *
     * @param string        $sSelector            The jQuery selector
     * @param string        $sContext             A context associated to the selector
     *
     * @return DomElement
     */
    public function jq(string $sSelector = '', string $sContext = '')
    {
        return $this->response->plugin('jquery')->element($sSelector, $sContext);
    }

    /**
     * Get an instance of a Jaxon class by name
     *
     * @param string $sName the class name
     *
     * @return object
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
    public function files()
    {
        return $this->jaxon->upload()->files();
    }

    /**
     * Get a data bag.
     *
     * @param string        $sName
     *
     * @return DataBagContext
     */
    public function bag(string $sName)
    {
        return $this->response->plugin('bags')->bag($sName);
    }
}
