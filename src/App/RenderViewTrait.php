<?php

namespace Jaxon\App;

use Jaxon\App\View\ViewRenderer;

trait RenderViewTrait
{
    /**
     * A prefix to apply to the rendered templates
     *
     * @var string
     */
    private string $sViewPrefix = '';

    /**
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    abstract protected function view(): ViewRenderer;

    /**
     * @param string $sViewPrefix
     *
     * @return static
     */
    final protected function setViewPrefix(string $sViewPrefix): static
    {
        $this->sViewPrefix = $sViewPrefix;
        return $this;
    }

    /**
     * Set the attached DOM node content with the component HTML code.
     *
     * @param string $sViewName    The view name
     * @param array $aViewData    The view data
     *
     * @return void
     */
    final protected function renderView(string $sViewName, array $aViewData = []): void
    {
        $this->before();
        $sHtml = $this->view()->render("{$this->sViewPrefix}$sViewName", $aViewData);
        $this->node()->html($sHtml === null ? '' : (string)$sHtml);
        $this->after();
    }
}
