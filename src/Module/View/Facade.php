<?php

namespace Jaxon\Module\View;

abstract class Facade
{
    protected $xStore = null;
    protected $aViewData;

    public function __construct()
    {
        $this->xStore = null;
        $this->aViewData = array();
    }

    /**
     * Get the current store or create a new store
     * 
     * @return Store        The current store
     */
    protected function store()
    {
        if(!$this->xStore)
        {
            $this->xStore = new Store($this);
        }
        return $this->xStore;
    }

    /**
     * Make a piece of data available for the rendered view
     *
     * @param string        $name            The data name
     * @param string        $value           The data value
     * 
     * @return void
     */
    public function set($name, $value)
    {
        $this->store()->with($name, $value);
        return $this;
    }

    /**
     * Make a piece of data available for all views
     *
     * @param string        $name            The data name
     * @param string        $value           The data value
     * 
     * @return void
     */
    public function share($name, $value)
    {
        $this->aViewData[$name] = $value;
        return $this;
    }

    /**
     * Render a view using a store
     *
     * The store returned by this function will later be used with the make() method to render the view.
     *
     * @param string        $sViewPath        The view path
     * @param array         $aViewData        The view data
     * 
     * @return Store        A store populated with the view data
     */
    public function render($sViewPath, array $aViewData = array())
    {
        // Get the store
        $store = $this->store();
        $store->setView($sViewPath, array_merge($this->aViewData, $aViewData));
        // Set the store to null so a new store will be created for the next view.
        $this->xStore = null;
        // Return the store
        return $store;
    }

    /**
     * Render a view using third party view system
     * 
     * @param Store         $store        A store populated with the view data
     * 
     * @return string        The string representation of the view
     */
    abstract public function make(Store $store);
}
