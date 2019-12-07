<?php

namespace Jaxon;

use Jaxon\Request\Support\CallableObject;

use Psr\Log\LoggerInterface;

class CallableClass
{
    /**
     * The Callable object associated to this class
     *
     * @var CallableObject
     */
    protected $callable = null;

    /**
     * The Jaxon response returned by all classes methods
     *
     * @var \Jaxon\Response\Response
     */
    protected $response = null;

    /**
     * Get the view renderer
     *
     * @return \Jaxon\Utils\View\Renderer
     */
    public function view()
    {
        return jaxon()->view();
    }

    /**
     * Get the session manager
     *
     * @return \Jaxon\Contracts\Session
     */
    public function session()
    {
        return jaxon()->session();
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger()
    {
        return jaxon()->logger();
    }

    /**
     * Get the request factory.
     *
     * @return \Jaxon\Request\Factory\CallableClass\Request
     */
    public function rq()
    {
        return jaxon()->di()->getCallableClassRequestFactory(get_class($this));
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
     * Get an instance of a Jaxon class by name
     *
     * @param string $name the class name
     *
     * @return CallableClass|null the Jaxon class instance, or null
     */
    public function cl($name)
    {
        $cFirstChar = substr($name, 0, 1);
        // If the class name starts with a dot, then find the class in the same full namespace as the caller
        if($cFirstChar == ':')
        {
            $name = $this->callable->getRootNamespace() . '\\' . str_replace('.', '\\', substr($name, 1));
        }
        // If the class name starts with a dot, then find the class in the same base namespace as the caller
        elseif($cFirstChar == '.')
        {
            $name = $this->callable->getNamespace() . '\\' . str_replace('.', '\\', substr($name, 1));
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
        return jaxon()->upload()->files();
    }
}
