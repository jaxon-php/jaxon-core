<?php

/**
 * Generator.php - Code generator interface
 *
 * Any class generating css or js code must implement this interface.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin;

use Jaxon\Ui\View\Renderer;
use Jaxon\Utils\Config\Config;

use function jaxon;

abstract class Package implements Code\Contracts\Generator
{
    /**
     * The configuration options of the package
     *
     * @var array
     */
    protected $aOptions = [];

    /**
     * The configuration options of the package
     *
     * @var Config
     */
    protected $xConfig;

    /**
     * Whether to include the getReadyScript() in the generated code.
     *
     * @var bool
     */
    protected $bReadyEnabled = false;

    /**
     * Get the package options in an array.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->aOptions;
    }

    /**
     * Get the package options in a Config object.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->xConfig;
    }

    /**
     * Get the value of a given package option
     *
     * @param string $sOption   The option name
     * @param mixed  $xDefault  The default value
     *
     * @return mixed
     */
    public function getOption(string $sOption, $xDefault = null)
    {
        return $this->getConfig()->getOption((string)$sOption, $xDefault);
    }

    /**
     * Get the view renderer
     *
     * @return Renderer
     */
    public function view(): Renderer
    {
        return jaxon()->view();
    }

    /**
     * Get the path to the config file
     *
     * @return string
     */
    abstract public static function getConfigFile(): string;

    /**
     * Include the getReadyScript() in the generated code.
     *
     * @return void
     */
    public function ready()
    {
        $this->bReadyEnabled = true;
    }

    /**
     * @inheritDoc
     */
    public function readyEnabled(): bool
    {
        return $this->bReadyEnabled;
    }

    /**
     * @inheritDoc
     */
    public final function readyInlined(): bool
    {
        // For packages, the getReadyScript() is never exported to external files.
        return true;
    }

    /**
     * @inheritDoc
     */
    public final function getHash(): string
    {
        // Packages do not generate hash on their own. So we make this method final.
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getCss(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getJs(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getScript(): string
    {
        return '';
    }

    /**
     * Get the HTML code of the package home page
     *
     * @return string
     */
    abstract public function getHtml(): string;

    /**
     * Get the HTML code of the package home page
     *
     * This method is an alias for getHtml().
     *
     * @return string
     */
    public function html(): string
    {
        return $this->getHtml();
    }
}
