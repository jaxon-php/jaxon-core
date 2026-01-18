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

use Jaxon\App\Session\SessionInterface;
use Jaxon\App\Stash\Stash;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Request\Target;
use Jaxon\Request\Upload\UploadHandlerInterface as UploadHandler;
use Psr\Log\LoggerInterface;

class ComponentHelper
{
    /**
     * @param ViewRenderer $xViewRenderer
     * @param LoggerInterface $xLogger
     * @param Stash $xStash
     * @param Target|null $xTarget
     * @param UploadHandler|null $xUploadHandler
     * @param SessionInterface|null $xSessionManager
     */
    public function __construct(public readonly ViewRenderer $xViewRenderer,
        public readonly LoggerInterface $xLogger, public readonly Stash $xStash,
        public readonly ?Target $xTarget, public readonly ?UploadHandler $xUploadHandler,
        public readonly ?SessionInterface $xSessionManager)
    {}
}
