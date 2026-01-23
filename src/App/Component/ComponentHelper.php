<?php

/**
 * ComponentHelper.php
 *
 * Provides helper functions to components.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Component;

use Jaxon\App\Pagination\PaginationRenderer;
use Jaxon\App\Session\SessionInterface;
use Jaxon\App\Stash\Stash;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Request\Upload\UploadHandlerInterface;
use Psr\Log\LoggerInterface;
use Closure;

class ComponentHelper
{
    /**
     * @var array
     */
    private array $extensions = [];

    /**
     * @param ViewRenderer $xViewRenderer
     * @param LoggerInterface $xLogger
     * @param Stash $xStash
     * @param UploadHandlerInterface|null $xUploadHandler
     * @param SessionInterface|null $xSessionManager
     * @param PaginationRenderer $xPaginationRenderer
     */
    public function __construct(public readonly ViewRenderer $xViewRenderer,
        public readonly LoggerInterface $xLogger, public readonly Stash $xStash,
        public readonly UploadHandlerInterface|null $xUploadHandler,
        public readonly SessionInterface|null $xSessionManager,
        public readonly PaginationRenderer $xPaginationRenderer)
    {}

    /**
     * @param string $target
     * @param Closure $extension
     *
     * @return self
     */
    final public function extend(string $target, Closure $extension): self
    {
        if($target === 'html' || $target === 'item')
        {
            $this->extensions[$target] ??= [];
            $this->extensions[$target][] = $extension;
        }

        // All other target values are ignored.
        return $this;
    }

    /**
     * @param string $target
     * @param string $value
     *
     * @return string
     */
    final public function extendValue(string $target, string $value): string
    {
        foreach(($this->extensions[$target] ?? []) as $extension)
        {
            $value = $extension($value);
        }
        return $value;
    }
}
