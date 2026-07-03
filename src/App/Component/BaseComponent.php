<?php

namespace Jaxon\App\Component;

use Jaxon\App\Component\ComponentHelper;
use Jaxon\App\Session\SessionInterface;
use Jaxon\App\Stash\Stash;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Request\CallableAction;
use Jaxon\Request\Upload\FileInterface;
use Psr\Log\LoggerInterface;

abstract class BaseComponent extends AbstractComponent
{
    /**
     * Initialize the component
     *
     * @return void
     */
    protected function setupComponent(): void
    {}

    /**
     * @return ComponentHelper
     */
    protected function helper(): ComponentHelper
    {
        return $this->factory()->helper();
    }

    /**
     * Get the Jaxon request action
     *
     * @return CallableAction|null
     */
    protected function action(): CallableAction|null
    {
        return $this->factory()->action();
    }

    /**
     * Get the Jaxon request action
     *
     * @return CallableAction|null
     * @deprecated Replaced by the action() method.
     */
    protected function target(): CallableAction|null
    {
        return $this->action();
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
     * Get the logger
     *
     * @return LoggerInterface
     */
    protected function logger(): LoggerInterface
    {
        return $this->helper()->xLogger;
    }

    /**
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    protected function view(): ViewRenderer
    {
        return $this->helper()->xViewRenderer;
    }

    /**
     * Get the session manager
     *
     * @return SessionInterface
     */
    protected function session(): SessionInterface
    {
        return $this->helper()->xSessionManager;
    }

    /**
     * Get the uploaded files
     *
     * @return array<FileInterface>
     */
    protected function files(): array
    {
        return $this->helper()->xUploadHandler->files();
    }
}
