<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\App\Session\SessionInterface;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Exception\SetupException;
use Jaxon\Script\JsCall;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Plugin\Response\DataBag\DataBagContext;
use Jaxon\Plugin\Response\Pagination\Paginator;
use Jaxon\Request\TargetInterface;
use Jaxon\Response\AjaxResponse;
use Psr\Log\LoggerInterface;

abstract class AbstractCallable
{
    /**
     * @var CallableClassHelper
     */
    protected $xCallableClassHelper = null;

    /**
     * Initialize the callable class
     *
     * @param Container $di
     *
     * @return void
     */
    abstract public function _initCallable(Container $di);

    /**
     * Get the Ajax response
     *
     * @return AjaxResponse
     */
    abstract protected function _response(): AjaxResponse;

    /**
     * Get the Jaxon request target
     *
     * @return TargetInterface
     */
    protected function target(): TargetInterface
    {
        return $this->xCallableClassHelper->xTarget;
    }

    /**
     * Get an instance of a Jaxon class by name
     *
     * @param string $sClassName the class name
     *
     * @return mixed
     * @throws SetupException
     */
    public function cl(string $sClassName)
    {
        return $this->xCallableClassHelper->cl($sClassName);
    }

    /**
     * Get the js call factory.
     *
     * @param string $sClassName
     *
     * @return JsCall
     */
    public function rq(string $sClassName = ''): JsCall
    {
        return $this->xCallableClassHelper->rq($sClassName);
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->xCallableClassHelper->xLogger;
    }

    /**
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    public function view(): ViewRenderer
    {
        return $this->xCallableClassHelper->xViewRenderer;
    }

    /**
     * Get the session manager
     *
     * @return SessionInterface
     */
    public function session(): SessionInterface
    {
        return $this->xCallableClassHelper->xSessionManager;
    }

    /**
     * Get the uploaded files
     *
     * @return array
     */
    public function files(): array
    {
        return $this->xCallableClassHelper->xUploadHandler->files();
    }

    /**
     * Get a data bag.
     *
     * @param string  $sBagName
     *
     * @return DataBagContext
     */
    public function bag(string $sBagName): DataBagContext
    {
        return $this->_response()->bag($sBagName);
    }

    /**
     * Render an HTML pagination control.
     *
     * @param int $nCurrentPage     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return Paginator
     */
    public function paginator(int $nCurrentPage, int $nItemsPerPage, int $nTotalItems): Paginator
    {
        return $this->_response()->paginator($nCurrentPage, $nItemsPerPage, $nTotalItems);
    }
}
