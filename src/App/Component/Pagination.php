<?php

namespace Jaxon\App\Component;

use Jaxon\App\Component\ComponentFactory;
use Jaxon\App\Pagination\NodePaginator;
use Jaxon\Di\Container;

class Pagination extends AbstractComponent
{
    use NodeResponseTrait;

    /**
     * @inheritDoc
     */
    final protected function initComponent(Container $di, ComponentFactory $xFactory): void
    {
        $this->setFactory($xFactory);
        $this->setNodeResponse($di);
    }

    /**
     * Create a paginator.
     *
     * @param int $nPageNumber      The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return NodePaginator
     */
    final public function paginator(int $nPageNumber, int $nItemsPerPage, int $nTotalItems): NodePaginator
    {
        return new NodePaginator($nPageNumber, $nItemsPerPage, $nTotalItems,
            $this->factory()->helper()->xPaginationRenderer, $this->node());
    }

    /**
     * Clear the attached DOM node content.
     *
     * @return void
     */
    final public function clear(): void
    {
        $this->node()->clear();
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
    }
}
