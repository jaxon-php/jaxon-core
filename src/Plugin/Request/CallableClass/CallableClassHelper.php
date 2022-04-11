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
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Request\Factory\ParameterFactory;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Handler\UploadHandler;
use Jaxon\Ui\View\ViewRenderer;
use Psr\Log\LoggerInterface;

class CallableClassHelper
{
    /**
     * @var RequestFactory
     */
    public $request;

    /**
     * @var ParameterFactory
     */
    public $parameter;

    /**
     * @var ViewRenderer
     */
    public $view;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var UploadHandler
     */
    public $upload;

    /**
     * @var CallableRegistry
     */
    public $registry;

    /**
     * @var SessionInterface
     */
    public $session;

    /**
     * The constructor
     *
     * @param Container $di
     * @param string $sClassName
     * @throws SetupException
     */
    public function __construct(Container $di, string $sClassName)
    {
        $this->request = $di->getFactory()->request($sClassName);
        $this->parameter = $di->getFactory()->parameter();
        $this->registry = $di->getCallableRegistry();
        $this->view = $di->getViewRenderer();
        $this->logger = $di->getLogger();
        $this->upload = $di->getUploadHandler();
        $this->session = $di->getSessionManager();
    }
}
