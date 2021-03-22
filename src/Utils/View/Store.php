<?php

namespace Jaxon\Utils\View;

use Jaxon\Contracts\View as ViewContract;
use JsonSerializable;

class Store
{
    /**
     * The view renderer
     *
     * @var ViewContract
     */
    protected $xRenderer;

    /**
     * The view namespace
     *
     * @var string
     */
    protected $sNamespace;

    /**
     * The view name
     *
     * @var string
     */
    protected $sViewName;

    /**
     * The view data
     *
     * @var array
     */
    protected $aViewData = [];

    /**
     * Make a piece of data available for the rendered view
     *
     * @param string        $name            The data name
     * @param string        $value           The data value
     *
     * @return Store
     */
    public function with($name, $value)
    {
        $this->aViewData[$name] = $value;
        return $this;
    }

    /**
     * Set the data to be rendered
     *
     * @param array         $aViewData        The view data
     *
     * @return void
     */
    public function setData(array $aViewData)
    {
        $this->aViewData = array_merge($this->aViewData, $aViewData);
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

    /**
     * Convert this object to string for json.
     *
     * This is a method of the JsonSerializable interface.
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->__toString();
    }
}
