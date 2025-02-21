<?php

namespace Jaxon\App;

use Jaxon\Di\Container;
use Jaxon\App\DataBag\DataBagContext;
use Jaxon\App\Session\SessionInterface;
use Jaxon\App\Stash\Stash;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Exception\SetupException;
use Jaxon\Script\JxnCall;
use Jaxon\Plugin\Request\CallableClass\ComponentHelper;
use Jaxon\Request\TargetInterface;
use Jaxon\Response\AjaxResponse;
use Psr\Log\LoggerInterface;

abstract class AbstractComponent
{
    /**
     * @var ComponentHelper
     */
    protected $xHelper = null;

    /**
     * Initialize the component
     *
     * @param Container $di
     * @param ComponentHelper $xHelper
     *
     * @return void
     */
    abstract public function _initComponent(Container $di, ComponentHelper $xHelper);

    /**
     * Get the Ajax response
     *
     * @return AjaxResponse
     */
    abstract protected function ajaxResponse(): AjaxResponse;

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
     * @return Stash
     */
    protected function stash(): Stash
    {
        return $this->xHelper->xStash;
    }

    /**
     * Get an instance of a Jaxon class by name
     *
     * @template T
     * @param class-string<T> $sClassName the class name
     *
     * @return T|null
     * @throws SetupException
     */
    public function cl(string $sClassName): mixed
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
        return $this->ajaxResponse()->bag($sBagName);
    }
}
