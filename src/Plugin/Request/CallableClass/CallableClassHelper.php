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
use Jaxon\Exception\SetupException;
use Jaxon\Script\Factory\CallFactory;
use Jaxon\Script\JxnCall;
use Jaxon\Request\Target;
use Jaxon\Request\Upload\UploadHandlerInterface;
use Psr\Log\LoggerInterface;

use function trim;

class CallableClassHelper
{
    /**
     * @var Target
     */
    public $xTarget;

    /**
     * The constructor
     *
     * @param JxnCall $xJxnCall
     * @param CallFactory $xFactory
     * @param CallableRegistry $xCallableRegistry
     * @param ViewRenderer $xViewRenderer
     * @param LoggerInterface $xLogger
     * @param SessionInterface $xSessionManager
     * @param UploadHandlerInterface|null $xUploadHandler
     *
     * @throws SetupException
     */
    public function __construct(public JxnCall $xJxnCall, public CallFactory $xFactory,
        public CallableRegistry $xCallableRegistry, public ViewRenderer $xViewRenderer,
        public LoggerInterface $xLogger, public ?SessionInterface $xSessionManager,
        public ?UploadHandlerInterface $xUploadHandler)
    {}

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
     * @return JxnCall
     */
    public function rq(string $sClassName = ''): JxnCall
    {
        $sClassName = trim($sClassName);
        return !$sClassName ? $this->xJxnCall : $this->xFactory->rq($sClassName);
    }
}
