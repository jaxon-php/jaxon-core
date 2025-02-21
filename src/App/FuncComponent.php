<?php

namespace Jaxon\App;

use Jaxon\App\Pagination\Paginator;
use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\Response;

class FuncComponent extends AbstractComponent
{
    /**
     * @var Response
     */
    protected $response = null;

    /**
     * @inheritDoc
     */
    public function _initComponent(Container $di, CallableClassHelper $xHelper)
    {
        $this->xHelper = $xHelper;
        $this->response = $di->getResponse();
    }

    /**
     * @inheritDoc
     */
    final protected function ajaxResponse(): AjaxResponse
    {
        return $this->response;
    }

    /**
     * Create a paginator.
     *
     * @param int $nPageNumber     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return Paginator
     */
    final public function paginator(int $nPageNumber, int $nItemsPerPage, int $nTotalItems): Paginator
    {
        return $this->ajaxResponse()->paginator($nPageNumber, $nItemsPerPage, $nTotalItems);
    }
}
