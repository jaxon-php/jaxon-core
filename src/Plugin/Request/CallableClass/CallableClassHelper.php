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
use Jaxon\JsCall\Factory;
use Jaxon\JsCall\JsFactory;
use Jaxon\Request\Target;
use Jaxon\Request\Upload\UploadHandlerInterface;
use Psr\Log\LoggerInterface;

use function trim;

class CallableClassHelper
{
    /**
     * @var Factory
     */
    public $xFactory;

    /**
     * @var JsFactory
     */
    public $xJsFactory;

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
        $this->xFactory = $di->getFactory();
        $this->xJsFactory = $this->xFactory->rq($sClassName);
        $this->xCallableRegistry = $di->getCallableRegistry();
        $this->xViewRenderer = $di->getViewRenderer();
        $this->xLogger = $di->getLogger();
        $this->xUploadHandler = $di->getUploadHandler();
        $this->xSessionManager = $di->getSessionManager();
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
        $sClassName = trim($sClassName);
        $xCallableObject = $this->xCallableRegistry->getCallableObject($sClassName);
        return !$xCallableObject ? null : $xCallableObject->getRegisteredObject();
    }

    /**
     * Get the js call factory.
     *
     * @param string $sClassName
     *
     * @return JsFactory
     */
    public function rq(string $sClassName = ''): JsFactory
    {
        $sClassName = trim($sClassName);
        return !$sClassName ? $this->xJsFactory : $this->xFactory->rq($sClassName);
    }
}
