<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\App\Cache\Cache;
use Jaxon\App\Session\SessionInterface;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Exception\SetupException;
use Jaxon\Script\JxnCall;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Plugin\Response\DataBag\DataBagContext;
use Jaxon\Request\TargetInterface;
use Jaxon\Response\AjaxResponse;
use Psr\Log\LoggerInterface;

abstract class AbstractCallable
{
    /**
     * @var CallableClassHelper
     */
    protected $xHelper = null;

    /**
     * Initialize the callable class
     *
     * @param Container $di
     * @param CallableClassHelper $xHelper
     *
     * @return void
     */
    abstract public function _initCallable(Container $di, CallableClassHelper $xHelper);

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
        return $this->xHelper->xTarget;
    }

    /**
     * Get the temp cache
     *
     * @return Cache
     */
    protected function cache(): Cache
    {
        return $this->xHelper->xCache;
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
        return $this->xHelper->cl($sClassName);
    }

    /**
     * Get the js call factory.
     *
     * @param string $sClassName
     *
     * @return JxnCall
     */
    public function rq(string $sClassName = ''): JxnCall
    {
        return $this->xHelper->rq($sClassName);
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->xHelper->xLogger;
    }

    /**
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    public function view(): ViewRenderer
    {
        return $this->xHelper->xViewRenderer;
    }

    /**
     * Get the session manager
     *
     * @return SessionInterface
     */
    public function session(): SessionInterface
    {
        return $this->xHelper->xSessionManager;
    }

    /**
     * Get the uploaded files
     *
     * @return array
     */
    public function files(): array
    {
        return $this->xHelper->xUploadHandler->files();
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
}
