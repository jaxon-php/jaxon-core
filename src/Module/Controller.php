<?php

namespace Jaxon\Module;

class Controller
{
    use \Jaxon\Request\Traits\Factory;

    /**
     * The Jaxon response returned by all controllers methods
     *
     * @var Jaxon\Response\Response
     */
    public $response = null;

    /**
     * A wrapper to the framework view component
     *
     * @var mixed
     */
    public $view = null;

    /**
     * The controller request factory
     *
     * @var Request\Factory
     */
    private $rqFactory;

    /**
     * The controller paginator
     *
     * @var Request\Paginator
     */
    private $pgFactory;

    /**
     * Create a new Controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->rqFactory = new Factory\Request($this);
        $this->pgFactory = new Factory\Paginator($this);
    }

    /**
     * Initialize the controller.
     *
     * @return void
     */
    public function init()
    {}

    /**
     * Get the request factory.
     *
     * @return Factory\Request
     */
    public function request()
    {
        return $this->rqFactory;
    }

    /**
     * Get the request factory.
     *
     * @return Factory\Request
     */
    public function rq()
    {
        return $this->request();
    }

    /**
     * Get the paginator factory.
     *
     * @param integer $nItemsTotal the total number of items
     * @param integer $nItemsPerPage the number of items per page
     * @param integer $nCurrentPage the current page
     *
     * @return Factory\Paginator
     */
    public function paginator($nItemsTotal, $nItemsPerPage, $nCurrentPage)
    {
        $this->pgFactory->setPaginationProperties($nItemsTotal, $nItemsPerPage, $nCurrentPage);
        return $this->pgFactory;
    }

    /**
     * Get the paginator factory.
     *
     * @param integer $nItemsTotal the total number of items
     * @param integer $nItemsPerPage the number of items per page
     * @param integer $nCurrentPage the current page
     *
     * @return Factory\Paginator
     */
    public function pg($nItemsTotal, $nItemsPerPage, $nCurrentPage)
    {
        return $this->paginator($nItemsTotal, $nItemsPerPage, $nCurrentPage);
    }

    /**
     * Create a JQuery Element with a given selector, and link it to the response attribute.
     * 
     * @param string        $sSelector            The jQuery selector
     * @param string        $sContext             A context associated to the selector
     *
     * @return Jaxon\JQuery\Dom\Element
     */
    public function jQuery($sSelector = '', string $sContext = '')
    {
        return $this->response->plugin('jquery')->element($sSelector, $sContext);
    }

    /**
     * Create a JQuery Element with a given selector, and link it to the response attribute.
     * 
     * @param string        $sSelector            The jQuery selector
     * @param string        $sContext             A context associated to the selector
     *
     * @return Jaxon\JQuery\Dom\Element
     */
    public function jq($sSelector = '', string $sContext = '')
    {
        return $this->jQuery($sSelector, $sContext);
    }

    /**
     * Find a Jaxon controller by name
     *
     * @param string $name the controller name
     * 
     * @return object the Jaxon controller, or null
     */
    public function controller($name)
    {
        // If the class name starts with a dot, then find the class in the same class path as the caller
        if(substr($name, 0, 1) == '.')
        {
            $name = $this->getJaxonClassPath() . $name;
        }
        // The controller namespace is prepended to the class name
        else if(($namespace = $this->getJaxonNamespace()))
        {
            $name = str_replace('\\', '.', trim($namespace, '\\')) . '.' . $name;
        }
        return jaxon()->module()->controller($name);
    }

    /**
     * Find a Jaxon controller by name
     *
     * @param string $name the controller name
     * 
     * @return object the Jaxon controller, or null
     */
    public function ct($name)
    {
        return $this->controller($name);
    }
}
