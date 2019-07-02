<?php

namespace Jaxon;

class CallableClass
{
    /**
     * The Jaxon response returned by all classes methods
     *
     * @var \Jaxon\Response\Response
     */
    public $response = null;

    /**
     * The callable object
     *
     * @var \Jaxon\Request\Support\CallableObject
     */
    public $xSupport = null;

    /**
     * Get the view renderer
     *
     * @return \Jaxon\Ui\View\Facade
     */
    public function view()
    {
        return jaxon()->di()->getViewRenderer();
    }

    /**
     * Get the session manager
     *
     * @return \Jaxon\Contracts\Session
     */
    public function session()
    {
        return jaxon()->di()->getSessionManager();
    }

    /**
     * Get the request factory.
     *
     * @return \Jaxon\Request\Factory\CallableClass\Request
     */
    public function rq()
    {
        return $this->xSupport->getRequestFactory();
    }

    /**
     * Get the request factory.
     *
     * @return \Jaxon\Request\Factory\CallableClass\Request
     */
    public function request()
    {
        return $this->rq();
    }

    /**
     * Get the paginator factory.
     *
     * @param integer $nItemsTotal the total number of items
     * @param integer $nItemsPerPage the number of items per page
     * @param integer $nCurrentPage the current page
     *
     * @return \Jaxon\Request\Factory\CallableClass\Paginator
     */
    public function pg($nItemsTotal, $nItemsPerPage, $nCurrentPage)
    {
        return $this->xSupport->getPaginatorFactory($nItemsTotal, $nItemsPerPage, $nCurrentPage);
    }

    /**
     * Get the paginator factory.
     *
     * @param integer $nItemsTotal the total number of items
     * @param integer $nItemsPerPage the number of items per page
     * @param integer $nCurrentPage the current page
     *
     * @return \Jaxon\Request\Factory\CallableClass\Paginator
     */
    public function paginator($nItemsTotal, $nItemsPerPage, $nCurrentPage)
    {
        return $this->pg($nItemsTotal, $nItemsPerPage, $nCurrentPage);
    }

    /**
     * Create a JQuery Element with a given selector, and link it to the response attribute.
     *
     * @param string        $sSelector            The jQuery selector
     * @param string        $sContext             A context associated to the selector
     *
     * @return \Jaxon\Response\Plugin\JQuery\Dom\Element
     */
    public function jq($sSelector = '', $sContext = '')
    {
        return $this->response->plugin('jquery')->element($sSelector, $sContext);
    }

    /**
     * Create a JQuery Element with a given selector, and link it to the response attribute.
     *
     * @param string        $sSelector            The jQuery selector
     * @param string        $sContext             A context associated to the selector
     *
     * @return \Jaxon\Response\Plugin\JQuery\Dom\Element
     */
    public function jQuery($sSelector = '', $sContext = '')
    {
        return $this->jq($sSelector, $sContext);
    }

    /**
     * Get an instance of a Jaxon class by name
     *
     * @param string $name the class name
     *
     * @return Jaxon\App\Armada|null the Jaxon class instance, or null
     */
    public function instance($name)
    {
        return $this->cl($name);
    }

    /**
     * Get an instance of a Jaxon class by name
     *
     * @param string $name the class name
     *
     * @return Jaxon\App\Callee|null the Jaxon class instance, or null
     */
    public function cl($name)
    {
        $cFirstChar = substr($name, 0, 1);
        // If the class name starts with a dot, then find the class in the same full namespace as the caller
        if($cFirstChar == ':')
        {
            $name = $this->xSupport->getRootNamespace() . '\\' . str_replace('.', '\\', substr($name, 1));
        }
        // If the class name starts with a dot, then find the class in the same base namespace as the caller
        elseif($cFirstChar == '.')
        {
            $name = $this->xSupport->getNamespace() . '\\' . str_replace('.', '\\', substr($name, 1));
        }
        // Find the class instance
        return jaxon()->instance($name);
    }

   /**
     * Get the uploaded files
     *
     * @return array
     */
    public function files()
    {
        return jaxon()->getUploadedFiles();
    }

   /**
     * Get the uploaded files
     *
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->files();
    }
}
