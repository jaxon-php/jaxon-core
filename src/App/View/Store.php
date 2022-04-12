<?php

namespace Jaxon\App\View;

use JsonSerializable;

use function array_merge;

class Store implements JsonSerializable
{
    /**
     * The view renderer
     *
     * @var ViewInterface
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
     * @param string $sName    The data name
     * @param mixed $xValue    The data value
     *
     * @return Store
     */
    public function with(string $sName, $xValue): Store
    {
        $this->aViewData[$sName] = $xValue;
        return $this;
    }

    /**
     * Set the data to be rendered
     *
     * @param array $aViewData    The view data
     *
     * @return Store
     */
    public function setData(array $aViewData): Store
    {
        $this->aViewData = array_merge($this->aViewData, $aViewData);
        return $this;
    }

    /**
     * Set the view to be rendered, with optional data
     *
     * @param ViewInterface $xRenderer    The view renderer
     * @param string $sNamespace    The view namespace
     * @param string $sViewName    The view name
     * @param array $aViewData    The view data
     *
     * @return Store
     */
    public function setView(ViewInterface $xRenderer, string $sNamespace, string $sViewName, array $aViewData = []): Store
    {
        $this->xRenderer = $xRenderer;
        $this->sNamespace = trim($sNamespace);
        $this->sViewName = trim($sViewName);
        $this->aViewData = array_merge($this->aViewData, $aViewData);
        return $this;
    }

    /**
     * Get the view namespace
     *
     * @return string        The view namespace
     */
    public function getNamespace(): string
    {
        return $this->sNamespace;
    }

    /**
     * Get the view name
     *
     * @return string        The view name
     */
    public function getViewName(): string
    {
        return $this->sViewName;
    }

    /**
     * Get the view data
     *
     * @return array         The view data
     */
    public function getViewData(): array
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
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}
