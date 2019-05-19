<?php

namespace Jaxon\App\View;

class Facade implements  \Jaxon\Contracts\Template\Renderer
{
    /**
     * The view data store
     *
     * @var Store
     */
    protected $xStore;

    /**
     * The view global data
     *
     * @var array
     */
    protected $aViewData;

    /**
     * The view renderers
     *
     * @var array
     */
    protected $aRenderers;

    /**
     * The default view namespace
     *
     * @var string
     */
    protected $sNamespace;

    /**
     * The constructor
     */
    public function __construct(Manager $xManager)
    {
        $this->xStore = null;
        $this->aViewData = [];
        $this->aRenderers = $xManager->getRenderers();
        $this->sNamespace = $xManager->getDefaultNamespace(); // The default view namespace
    }

    /**
     * Get the current store or create a new store
     *
     * @return Store
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
     * @return Facade
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
     * @return Facade
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
    public function render($sViewName, array $aViewData = [])
    {
        // Get the store
        $xStore = $this->store();
        // Get the default view namespace
        $sNamespace = $this->sNamespace;
        // Get the namespace from the view name
        $iSeparatorPosition = strrpos($sViewName, '::');
        if($iSeparatorPosition !== false)
        {
            $sNamespace = substr($sViewName, 0, $iSeparatorPosition);
        }
        if(!key_exists($sNamespace, $this->aRenderers))
        {
            // Cannot render a view if there's no renderer corresponding to the namespace.
            return null;
        }
        $sRenderer = $this->aRenderers[$sNamespace];
        $xStore->setView($sRenderer, $sNamespace, $sViewName, array_merge($this->aViewData, $aViewData));
        // Set the store to null so a new store will be created for the next view.
        $this->xStore = null;
        // Return the store
        return $xStore;
    }
}
