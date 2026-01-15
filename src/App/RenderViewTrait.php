<?php

namespace Jaxon\App;

use Jaxon\App\View\ViewRenderer;

use function Jaxon\jaxon;

trait RenderViewTrait
{
    /**
     * A prefix to apply to the rendered templates
     *
     * @var string
     */
    private string $sViewPrefix = '';

    /**
     * @var ViewRenderer
     */
    private static ViewRenderer $xViewRenderer = null;

    /**
     * @return ViewRenderer
     */
    private static function renderer(): ViewRenderer
    {
        return self::$xViewRenderer ??= jaxon()->view();
    }

    /**
     * @param string $sViewPrefix
     *
     * @return self
     */
    final protected function setViewPrefix(string $sViewPrefix): self
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
    final public function renderView(string $sViewName, array $aViewData = []): void
    {
        $sHtml = self::renderer()->render("{$this->sViewPrefix}$sViewName", $aViewData);
        $this->before();
        $this->node()->html($sHtml === null ? '' : (string)$sHtml);
        $this->after();
    }
}
