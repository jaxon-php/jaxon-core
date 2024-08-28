<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Plugin\Response\Pagination\Paginator;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\ComponentResponse;

use function get_class;

abstract class PaginationComponent extends AbstractCallable
{
    /**
     * @var ComponentResponse
     */
    private $response = null;

    /**
     * @var int
     */
    private int $nPageNumber;

    /**
     * @var int
     */
    private int $nTotalItems;

    /**
     * @inheritDoc
     */
    public function _initCallable(Container $di, CallableClassHelper $xCallableClassHelper)
    {
        $this->xCallableClassHelper = $xCallableClassHelper;
        // Each component must have its own reponse object.
        $this->response = $di->newComponentResponse(get_class($this));
    }

    /**
     * @inheritDoc
     */
    final protected function _response(): AjaxResponse
    {
        return $this->response;
    }

    /**
     * Get the number of items to show per page.
     *
     * @return int
     */
    abstract protected function itemsPerPage(): int;

    /**
     * Set the pagination options
     *
     * @param int $nPageNumber
     * @param int $nTotalItems
     *
     * @return Paginator
     */
    final public function paginator(int $nPageNumber, int $nTotalItems): Paginator
    {
        return $this->_response()->paginator($nPageNumber, $this->itemsPerPage(), $nTotalItems);
    }
}
