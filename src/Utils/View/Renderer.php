<?php

namespace Jaxon\Utils\View;

class Renderer
{
    /**
     * The view data store
     *
     * @var Store
     */
    protected $xStore = null;

    /**
     * The view global data
     *
     * @var array
     */
    protected $aViewData = [];

    /**
     * The view manager
     *
     * @var Manager
     */
    protected $xManager;

    /**
     * The constructor
     */
    public function __construct(Manager $xManager)
    {
        $this->xManager = $xManager;
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
            $this->xStore = new Store();
        }
        return $this->xStore;
    }

    /**
     * Make a piece of data available for the rendered view
     *
     * @param string        $name            The data name
     * @param string        $value           The data value
     *
     * @return Renderer
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
     * @return Renderer
     */
    public function share($name, $value)
    {
        $this->aViewData[$name] = $value;
        return $this;
    }

    /**
     * Make an array of data available for all views
     *
     * @param array         $values          The data values
     *
     * @return Renderer
     */
    public function shareValues(array $values)
    {
        foreach($values as $name => $value)
        {
            $this->share($name, $value);
        }
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
     * @return null|Store   A store populated with the view data
     */
    public function render($sViewName, array $aViewData = [])
    {
        // Get the store
        $xStore = $this->store();

        // Get the default view namespace
        $sNamespace = $this->xManager->getDefaultNamespace();
        // Get the namespace from the view name
        $iSeparatorPosition = strrpos($sViewName, '::');
        if($iSeparatorPosition !== false)
        {
            $sNamespace = substr($sViewName, 0, $iSeparatorPosition);
        }

        $xRenderer = $this->xManager->getNamespaceRenderer($sNamespace);
        if(!$xRenderer)
        {
            // Cannot render a view if there's no renderer corresponding to the namespace.
            return null;
        }

        $xStore->setData(\array_merge($this->aViewData, $aViewData))
            ->setView($xRenderer, $sNamespace, $sViewName);
        // Set the store to null so a new store will be created for the next view.
        $this->xStore = null;
        // Return the store
        return $xStore;
    }
}
