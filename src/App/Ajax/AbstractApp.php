<?php

/**
 * AbstractApp.php
 *
 * Base class for Jaxon applications.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax;

use Jaxon\App\Ajax\Bootstrap;
use Jaxon\App\Ajax\Jaxon;
use Psr\Container\ContainerInterface;

abstract class AbstractApp implements AppInterface
{
    use Traits\ServicesTrait;
    use Traits\PluginTrait;
    use Traits\RequestTrait;
    use Traits\ResponseTrait;

    /**
     * The class constructor
     */
    public function __construct()
    {
        // Declared in DiTrait.
        $this->xContainer = Jaxon::getInstance()->di();
        $this->xComponentContainer = Jaxon::getInstance()->cdi();
    }

    /**
     * Get the Jaxon application bootstrapper.
     *
     * @return Bootstrap
     */
    protected function bootstrap(): Bootstrap
    {
        return $this->xContainer->getBootstrap();
    }

    /**
     * Set the javascript or css asset
     *
     * @param bool $bExport    Whether to export the code in a file
     * @param bool $bMinify    Whether to minify the exported file
     * @param string $sUri     The URI to access the exported file
     * @param string $sDir     The directory where to create the file
     * @param string $sType    The asset type: "js" or "css"
     *
     * @return void
     */
    public function asset(bool $bExport, bool $bMinify,
        string $sUri = '', string $sDir = '', string $sType = ''): void
    {
        $this->bootstrap()->asset($bExport, $bMinify, $sUri, $sDir, $sType);
    }

    /**
     * Set the container provided by the integrated framework
     *
     * @param ContainerInterface $xContainer    The container implementation
     *
     * @return void
     */
    public function setContainer(ContainerInterface $xContainer): void
    {
        $this->di()->setContainer($xContainer);
    }

    /**
     * @inheritDoc
     */
    public function setup(string $sConfigFile = ''): void
    {}
}
