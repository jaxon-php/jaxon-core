<?php

namespace Jaxon\Module\View;

class Facade
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\View;

    protected $xStore;
    protected $aViewData;
    protected $aRenderers;
    protected $sNamespace;

    public function __construct($aRenderers, $sNamespace)
    {
        $this->xStore = null;
        $this->aViewData = array();
        $this->aRenderers = $aRenderers;
        $this->sNamespace = $sNamespace; // The default view namespace
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
     * @param string        $sViewName        The view name
     * @param array         $aViewData        The view data
     * 
     * @return Store        A store populated with the view data
     */
    public function render($sViewName, array $aViewData = array())
    {
        // Get the store
        $store = $this->store();
        // Get the default view namespace
        $sNamespace = $this->sNamespace;
        // Get the namespace from the view name
        $iSeparatorPosition = strrpos($sViewName, '::');
        if($iSeparatorPosition !== false)
        {
            $sNamespace = substr($sViewName, 0, $iSeparatorPosition);
        }
        else
        {
            $sViewName = $sNamespace . '::' . $sViewName;
        }
        if(!key_exists($sNamespace, $this->aRenderers))
        {
            // Cannot render a view if there's no renderer corresponding to the namespace.
            return null;
        }
        $sRenderer = $this->aRenderers[$sNamespace];
        $store->setView($sRenderer, $sViewName, array_merge($this->aViewData, $aViewData));
        // Set the store to null so a new store will be created for the next view.
        $this->xStore = null;
        // Return the store
        return $store;
    }
}
