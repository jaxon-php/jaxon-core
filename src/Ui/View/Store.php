<?php

namespace Jaxon\Ui\View;

use Jaxon\Contracts\App\View as ViewContract;

class Store
{
    protected $xRenderer;
    protected $sNamespace;
    protected $sViewName;
    protected $aViewData = [];

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
     * @param ViewContract  $xRenderer        The view renderer
     * @param string        $sNamespace       The view namespace
     * @param string        $sViewName        The view name
     * @param array         $aViewData        The view data
     *
     * @return void
     */
    public function setView(ViewContract $xRenderer, $sNamespace, $sViewName, array $aViewData = [])
    {
        $this->xRenderer = $xRenderer;
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
        return ($this->xRenderer) ? $this->xRenderer->render($this) : '';
    }
}
