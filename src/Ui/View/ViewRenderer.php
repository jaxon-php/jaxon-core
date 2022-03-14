<?php

namespace Jaxon\Ui\View;

use function strrpos;
use function substr;
use function array_merge;

class ViewRenderer
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
     * @var ViewManager
     */
    protected $xViewManager;

    /**
     * The constructor
     */
    public function __construct(ViewManager $xViewManager)
    {
        $this->xViewManager = $xViewManager;
    }

    /**
     * Get the current store or create a new store
     *
     * @return Store
     */
    protected function store(): Store
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
     * @param string $sName    The data name
     * @param mixed $xValue    The data value
     *
     * @return ViewRenderer
     */
    public function set(string $sName, $xValue): ViewRenderer
    {
        $this->store()->with($sName, $xValue);
        return $this;
    }

    /**
     * Make a piece of data available for all views
     *
     * @param string $sName    The data name
     * @param mixed $xValue    The data value
     *
     * @return ViewRenderer
     */
    public function share(string $sName, $xValue): ViewRenderer
    {
        $this->aViewData[$sName] = $xValue;
        return $this;
    }

    /**
     * Make an array of data available for all views
     *
     * @param array $aValues    The data values
     *
     * @return ViewRenderer
     */
    public function shareValues(array $aValues): ViewRenderer
    {
        foreach($aValues as $sName => $xValue)
        {
            $this->share($sName, $xValue);
        }
        return $this;
    }

    /**
     * Render a view using a store
     *
     * The store returned by this function will later be used with the make() method to render the view.
     *
     * @param string $sViewName    The view name
     * @param array $aViewData    The view data
     *
     * @return null|Store   A store populated with the view data
     */
    public function render(string $sViewName, array $aViewData = []): ?Store
    {
        // Get the store
        $xStore = $this->store();

        // Get the default view namespace
        $sNamespace = $this->xViewManager->getDefaultNamespace();
        // Get the namespace from the view name
        $nSeparatorPosition = strrpos($sViewName, '::');
        if($nSeparatorPosition !== false)
        {
            $sNamespace = substr($sViewName, 0, $nSeparatorPosition);
        }

        $xRenderer = $this->xViewManager->getNamespaceRenderer($sNamespace);
        if(!$xRenderer)
        {
            // Cannot render a view if there's no renderer corresponding to the namespace.
            return null;
        }

        $xStore->setData(array_merge($this->aViewData, $aViewData))
            ->setView($xRenderer, $sNamespace, $sViewName);
        // Set the store to null so a new store will be created for the next view.
        $this->xStore = null;
        // Return the store
        return $xStore;
    }
}
