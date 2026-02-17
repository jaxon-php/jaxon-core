<?php

namespace Jaxon\App\Component;

use Jaxon\App\Component\ComponentHelper;
use Jaxon\App\Session\SessionInterface;
use Jaxon\App\Stash\Stash;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Request\TargetInterface;
use Jaxon\Request\Upload\FileInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;

abstract class BaseComponent extends AbstractComponent
{
    /**
     * @var array
     */
    private array $aComponentData = [];

    /**
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return static
     */
    public function set(string $sKey, mixed $xValue): static
    {
        $this->aComponentData[$sKey] = $xValue;
        return $this;
    }

    /**
     * @param string $sKey
     *
     * @return bool
     */
    public function has(string $sKey): bool
    {
        return array_key_exists($sKey, $this->aComponentData);
    }

    /**
     * @param string $sKey
     * @param mixed $xDefault
     *
     * @return mixed
     */
    public function get(string $sKey, mixed $xDefault = null): mixed
    {
        return $this->aComponentData[$sKey] ?? $xDefault;
    }

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
     * Get the Jaxon request target
     *
     * @return TargetInterface|null
     */
    protected function target(): TargetInterface|null
    {
        return $this->factory()->target();
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
