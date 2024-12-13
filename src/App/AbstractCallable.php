<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\App\Ajax\Cache\Cache;
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
     * The temp cache
     *
     * @var Cache
     */
    protected $temp = null;

    /**
     * @var CallableClassHelper
     */
    protected $xCallableClassHelper = null;

    /**
     * Initialize the callable class
     *
     * @param Container $di
     * @param CallableClassHelper $xCallableClassHelper
     *
     * @return void
     */
    abstract public function _initCallable(Container $di, CallableClassHelper $xCallableClassHelper);

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
     * @return JxnCall
     */
    public function rq(string $sClassName = ''): JxnCall
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
}
