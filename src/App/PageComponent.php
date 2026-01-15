<?php

namespace Jaxon\App;

use Jaxon\App\Pagination\PageNumberInput;
use Jaxon\App\Pagination\Paginator;
use Jaxon\Script\JsExpr;
use Closure;

use function is_a;

abstract class PageComponent extends NodeComponent
{
    /**
     * @var Closure|null
     */
    protected Closure|null $fPaginatorSetup = null;

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
     * Get the paginator for the component.
     *
     * @param Closure|int $xOption
     *
     * @return PageComponent|Paginator
     */
    final protected function paginator(Closure|int $xOption): PageComponent|Paginator
    {
        if(is_a($xOption, Closure::class))
        {
            $this->fPaginatorSetup = $xOption;
            return $this;
        }

        $pageNumber = $this->input()->getInputPageNumber($xOption);
        $paginator = $this->cl(Component\Pagination::class)
            // Use the js class name as component item identifier.
            ->item($this->rq()->_class())
            // This call will also set the current page number value.
            ->paginator($pageNumber, $this->limit(), $this->count())
            // This callback will receive the final value of the current page number.
            ->page($this->input()->setFinalPageNumber(...));

        // Pass the paginator to the setup closure, if one was provided.
        if($this->fPaginatorSetup !== null)
        {
            ($this->fPaginatorSetup)($paginator);
        }

        return $paginator;
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
        // Get the paginator for the component.
        $paginator = $this->paginator($pageNumber);
        // Now the page number is set, the page content can be rendered.
        $this->render();
        // Render the pagination component.
        $paginator->render($xCall);
    }
}
