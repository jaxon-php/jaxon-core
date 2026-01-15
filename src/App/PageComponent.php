<?php

namespace Jaxon\App;

use Jaxon\App\Pagination\PageNumberInput;
use Jaxon\Script\JsExpr;

abstract class PageComponent extends NodeComponent
{
    /**
     * @var PageNumberInput|null
     */
    private PageNumberInput|null $xInput = null;

    /**
     * @return PageNumberInput
     */
    protected function makeInput(): PageNumberInput
    {
        return new PageNumberInput();
    }

    /**
     * @return PageNumberInput
     */
    private function input(): PageNumberInput
    {
        return $this->xInput ??= $this->makeInput();
    }

    /**
     * Get the total number of items to paginate.
     *
     * @return int
     */
    abstract protected function count(): int;

    /**
     * Get the max number of items per page.
     *
     * @return int
     */
    abstract protected function limit(): int;

    /**
     * Get the current page number
     *
     * @return int
     */
    final protected function currentPage(): int
    {
        return $this->input()->getFinalPageNumber();
    }

    /**
     * Render the page and pagination components
     *
     * @param JsExpr $xCall
     * @param int $pageNumber
     *
     * @return void
     */
    final protected function paginate(JsExpr $xCall, int $pageNumber): void
    {
        $pageNumber = $this->input()->getInputPageNumber($pageNumber);
        // Get the pagination component.
        $paginator = $this->cl(Component\Pagination::class)
            // Use the js class name as component item identifier.
            ->item($this->rq()->_class())
            // This call will also set the current page number value.
            ->paginator($pageNumber, $this->limit(), $this->count())
            // This callback will receive the final value of the current page number.
            ->page($this->input()->setFinalPageNumber(...));
        // Now the page number is set, the page content can be rendered.
        $this->render();
        // Render the pagination component.
        $paginator->render($xCall);
    }
}
