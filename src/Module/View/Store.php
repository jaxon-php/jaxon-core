<?php

namespace Jaxon\Module\View;

class Store
{
    protected $xFacade;
    protected $aViewData;
    protected $sViewPath;

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
     * @param string        $sViewPath        The view path
     * @param string        $aViewData        The view data
     * 
     * @return void
     */
    public function setView($sViewPath, array $aViewData = array())
    {
        $this->sViewPath = trim($sViewPath);
        $this->aViewData = array_merge($this->aViewData, $aViewData);
    }

    /**
     * Get the view path
     * 
     * @return string        The view path
     */
    public function getViewPath()
    {
        return $this->sViewPath;
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
     * Render the view
     * 
     * @return string        The string representation of the view
     */
    public function __toString()
    {
        return $this->xFacade->make($this);
    }
}
