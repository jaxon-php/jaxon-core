<?php

namespace Jaxon\App\Component;

use Jaxon\App\DataBag\DataBagContext;
use Jaxon\App\Session\SessionInterface;
use Jaxon\App\Stash\Stash;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\ComponentHelper;
use Jaxon\Request\TargetInterface;
use Jaxon\Response\AjaxResponse;
use Psr\Log\LoggerInterface;

trait ComponentTrait
{
    /**
     * Get the component helper
     *
     * @return ComponentHelper
     */
    abstract protected function helper(): ComponentHelper;

    /**
     * Get the Ajax response
     *
     * @return AjaxResponse
     */
    abstract protected function response(): AjaxResponse;

    /**
     * Get the Jaxon request target
     *
     * @return TargetInterface
     */
    protected function target(): TargetInterface
    {
        return $this->helper()->xTarget;
    }

    /**
     * Get the temp cache
     *
     * @return Stash
     */
    protected function stash(): Stash
    {
        return $this->helper()->xStash;
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
        return $this->helper()->cl($sClassName);
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->helper()->xLogger;
    }

    /**
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    public function view(): ViewRenderer
    {
        return $this->helper()->xViewRenderer;
    }

    /**
     * Get the session manager
     *
     * @return SessionInterface
     */
    public function session(): SessionInterface
    {
        return $this->helper()->xSessionManager;
    }

    /**
     * Get the uploaded files
     *
     * @return array
     */
    public function files(): array
    {
        return $this->helper()->xUploadHandler->files();
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
        return $this->response()->bag($sBagName);
    }
}
