<?php

namespace Jaxon\App;

use Jaxon\App\Pagination\NodePaginator;
use Jaxon\App\Pagination\PageNumberInput;
use Jaxon\Script\JsExpr;
use Closure;

use function is_a;

abstract class PageComponent extends Component\NodeComponent
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
     * @var string
     */
    private string $sPaginationComponent = Component\Pagination::class;

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
     * @return string
     */
    private function paginationComponentItem(): string
    {
        // Use the js class name as the pagination component item identifier.
        return $this->helper()->extendValue('item', $this->rq()->_class());
    }

    /**
     * Get the attributes to bind the pagination component.
     *
     * @return array<string>
     */
    final public function paginationAttributes(): array
    {
        return [
            $this->rq($this->sPaginationComponent)->_class(),
            $this->paginationComponentItem(),
        ];
    }

    /**
     * Get the paginator for the component.
     *
     * @param Closure $fSetup
     *
     * @return static
     */
    final protected function paginatorSetup(Closure $fSetup): static
    {
         $this->fPaginatorSetup = $fSetup;
        return $this;
    }

    /**
     * Get the paginator for the component.
     *
     * @param class-string $sComponent
     *
     * @return static
     */
    final protected function paginatorComponent(string $sComponent): static
    {
        // Invalid values are ignored.
        if(is_a($sComponent, Component\Pagination::class, true))
        {
            $this->sPaginationComponent = $sComponent;
        }
        return $this;
    }

    /**
     * Get the paginator for the component.
     *
     * @param int $nPageNumber
     *
     * @return NodePaginator
     */
    final protected function paginator(int $nPageNumber): NodePaginator
    {
        $nPageNumber = $this->input()->getInputPageNumber($nPageNumber);
        $paginator = $this->cl($this->sPaginationComponent)
            ->item($this->paginationComponentItem())
            // This call will also set the current page number value.
            ->paginator($nPageNumber, $this->limit(), $this->count())
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
     * Render the page and pagination components.
     *
     * @param JsExpr $xCall
     * @param int $nPageNumber
     *
     * @return void
     */
    final protected function paginate(JsExpr $xCall, int $nPageNumber): void
    {
        // Get the paginator for the component.
        $paginator = $this->paginator($nPageNumber);
        // Now the page number is set, the page content can be rendered.
        $this->render();
        // Render the pagination component.
        $paginator->render($xCall);
    }

    /**
     * Clear the attached DOM node content.
     *
     * @return void
     */
    final public function clear(): void
    {
        $this->node()->clear();
        // Also clear the related pagination component.
        $this->cl($this->sPaginationComponent)
            ->item($this->paginationComponentItem())
            ->clear();
    }

    /**
     * Show/hide the attached DOM node.
     *
     * @param bool $bVisible
     *
     * @return void
     */
    final public function visible(bool $bVisible): void
    {
        $bVisible ? $this->node()->jq()->show() : $this->node()->jq()->hide();
        // Also show/hide the related pagination component.
        $this->cl($this->sPaginationComponent)
            ->item($this->paginationComponentItem())
            ->visible($bVisible);
    }
}
