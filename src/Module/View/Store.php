<?php

namespace Jaxon\Module\View;

class Store
{
    protected $xFacade;
    protected $sRenderer;
    protected $sNamespace;
    protected $sViewName;
    protected $aViewData;

    public function __construct(Facade $xFacade)
    {
        $this->xFacade = $xFacade;
        $this->aViewData = array();
    }

    /**
     * Make a piece of data available for the rendered view
     *
     * @param string        $name            The data name
     * @param string        $value           The data value
     * 
     * @return void
     */
    public function with($name, $value)
    {
        $this->aViewData[$name] = $value;
        return $this;
    }

    /**
     * Set the view to be rendered, with optional data
     *
     * @param string        $sRenderer        The view renderer
     * @param string        $sNamespace       The view namespace
     * @param string        $sViewName        The view name
     * @param string        $aViewData        The view data
     * 
     * @return void
     */
    public function setView($sRenderer, $sNamespace, $sViewName, array $aViewData = array())
    {
        $this->sRenderer = trim($sRenderer);
        $this->sNamespace = trim($sNamespace);
        $this->sViewName = trim($sViewName);
        $this->aViewData = array_merge($this->aViewData, $aViewData);
    }

    /**
     * Get the view namespace
     * 
     * @return string        The view namespace
     */
    public function getNamespace()
    {
        return $this->sNamespace;
    }

    /**
     * Get the view name
     * 
     * @return string        The view name
     */
    public function getViewName()
    {
        return $this->sViewName;
    }

    /**
     * Get the view data
     * 
     * @return array         The view data
     */
    public function getViewData()
    {
        return $this->aViewData;
    }

    /**
     * Render a view using third party view system
     * 
     * @return string        The string representation of the view
     */
    public function __toString()
    {
        $xRenderer = $this->xFacade->getViewRenderer($this->sRenderer);
        return ($xRenderer) ? $xRenderer->make($this) : '';
    }
}
