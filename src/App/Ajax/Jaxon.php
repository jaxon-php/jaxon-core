<?php

/**
 * Jaxon.php
 *
 * The Jaxon class uses a modular plug-in system to facilitate the processing
 * of special Ajax requests made by a PHP page.
 * It generates Javascript that the page must include in order to make requests.
 * It handles the output of response commands (see <Jaxon\Response\Response>).
 * Many flags and settings can be adjusted to effect the behavior of the Jaxon class
 * as well as the client-side javascript.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax;

use Jaxon\Di\ComponentContainer;
use Jaxon\Di\Container;

final class Jaxon
{
    use Traits\ConfigTrait;
    use Traits\ServicesTrait;
    use Traits\PluginTrait;
    use Traits\RequestTrait;
    use Traits\ResponseTrait;
    use Traits\SendResponseTrait;

    /**
     * @var Jaxon|null
     */
    private static $xInstance = null;

    /**
     * The constructor
     *
     * @param Container $xContainer
     * @param ComponentContainer $xComponentContainer
     */
    private function __construct(Container $xContainer, ComponentContainer $xComponentContainer)
    {
        $this->xContainer = $xContainer;
        $this->xComponentContainer = $xComponentContainer;
    }

    /**
     * @return Jaxon
     */
    private static function createInstance(): Jaxon
    {
        $xContainer = new Container();
        $xComponentContainer = new ComponentContainer($xContainer);
        self::$xInstance = new Jaxon($xContainer, $xComponentContainer);

        // Save the Jaxon and container instances
        $xContainer->val(Jaxon::class, self::$xInstance);
        $xContainer->val(ComponentContainer::class, $xComponentContainer);

        // Make the helpers functions available in the global namespace.
        self::$xInstance->callback()->boot(fn() => self::$xInstance->config()->globals());

        return self::$xInstance;
    }

    /**
     * @return Jaxon
     */
    public static function getInstance(): Jaxon
    {
        return self::$xInstance ?: self::$xInstance = self::createInstance();
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return \Jaxon\Jaxon::VERSION;
    }

    /**
     * Set the ajax endpoint URI
     *
     * @param string $sUri    The ajax endpoint URI
     *
     * @return void
     */
    public function setUri(string $sUri): void
    {
        $this->config()->setOption('core.request.uri', $sUri);
    }

    /**
     * @return AppInterface
     */
    public function app(): AppInterface
    {
        return $this->xContainer->getApp();
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        self::$xInstance = null;
    }
}
