<?php

/**
 * CallableClassHelper.php
 *
 * Provides properties to a callable class.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Request\CallableClass;

use Jaxon\App\Session\SessionInterface;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Target;
use Jaxon\Request\Upload\UploadHandlerInterface;
use Psr\Log\LoggerInterface;

class CallableClassHelper
{
    /**
     * @var RequestFactory
     */
    public $xRequestFactory;

    /**
     * @var ViewRenderer
     */
    public $xViewRenderer;

    /**
     * @var LoggerInterface
     */
    public $xLogger;

    /**
     * @var UploadHandlerInterface
     */
    public $xUploadHandler;

    /**
     * @var CallableRegistry
     */
    public $xCallableRegistry;

    /**
     * @var SessionInterface
     */
    public $xSessionManager;

    /**
     * @var Target
     */
    public $xTarget;

    /**
     * The constructor
     *
     * @param Container $di
     * @param string $sClassName
     *
     * @throws SetupException
     */
    public function __construct(Container $di, string $sClassName)
    {
        $this->xRequestFactory = $di->getFactory()->request($sClassName);
        $this->xCallableRegistry = $di->getCallableRegistry();
        $this->xViewRenderer = $di->getViewRenderer();
        $this->xLogger = $di->getLogger();
        $this->xUploadHandler = $di->getUploadHandler();
        $this->xSessionManager = $di->getSessionManager();
    }
}
