<?php

/**
 * AbstractResponse.php - Base class for Jaxon responses
 *
 * This class collects commands to be sent back to the browser in response to a jaxon request.
 * Commands are encoded and packaged in json format.
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

namespace Jaxon\Response;

use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\AbstractResponsePlugin;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

abstract class AbstractResponse
{
    /**
     * @var ResponseManager
     */
    protected $xManager;

    /**
     * @var PluginManager
     */
    protected $xPluginManager;

    /**
     * The constructor
     *
     * @param ResponseManager $xManager
     * @param PluginManager $xPluginManager
     */
    public function __construct(ResponseManager $xManager, PluginManager $xPluginManager)
    {
        $this->xManager = $xManager;
        $this->xPluginManager = $xPluginManager;
    }

    /**
     * @inheritDoc
     */
    abstract public function getContentType(): string;

    /**
     * @inheritDoc
     */
    abstract public function getOutput(): string;

    /**
     * Convert this response to a PSR7 response object
     *
     * @return PsrResponseInterface
     */
    abstract public function toPsr(): PsrResponseInterface;

    /**
     * Convert to string
     *
     * @param mixed $xData
     *
     * @return string
     */
    protected function str($xData): string
    {
        return $this->xManager->str($xData);
    }

    /**
     * Get the error message
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->xManager->getErrorMessage();
    }

    /**
     * Get the commands in the response
     *
     * @return array
     */
    public function getCommands(): array
    {
        return $this->xManager->getCommands();
    }

    /**
     * Get the number of commands in the response
     *
     * @return int
     */
    public function getCommandCount(): int
    {
        return $this->xManager->getCommandCount();
    }

    /**
     * Clear all the commands already added to the response
     *
     * @return void
     */
    public function clearCommands()
    {
        $this->xManager->clearCommands();
    }

    /**
     * Provides access to registered response plugins
     *
     * @param string $sName    The name of the plugin
     *
     * @return null|AbstractResponsePlugin
     */
    public function plugin(string $sName): ?AbstractResponsePlugin
    {
        $xResponsePlugin = $this->xPluginManager->getResponsePlugin($sName);
        if($xResponsePlugin !== null)
        {
            $xResponsePlugin->_init($this, $this->xManager);
        }
        return $xResponsePlugin;
    }

    /**
     * Magic PHP function
     *
     * Used to permit plugins to be called as if they were native members of the Response instance.
     *
     * @param string $sPluginName    The name of the plugin
     *
     * @return null|AbstractResponsePlugin
     */
    public function __get(string $sPluginName)
    {
        return $this->plugin($sPluginName);
    }
}
