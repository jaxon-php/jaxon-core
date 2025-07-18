<?php

/**
 * AbstractResponsePlugin.php
 *
 * Interface for Jaxon Response plugins.
 *
 * A response plugin provides additional services not already provided by the
 * <Jaxon\Response\Response> class with regard to sending response commands to the client.
 * In addition, a response command may send javascript to the browser at page load
 * to aid in the processing of its response commands.
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

namespace Jaxon\Plugin;

use Jaxon\Response\AbstractResponse;
use Jaxon\Response\Manager\Command;
use JsonSerializable;

trait ResponsePluginTrait
{
    /**
     * The object used to build the response that will be sent to the client browser
     *
     * @var AbstractResponse
     */
    private $xResponse = null;

    /**
     * Get a unique name to identify the plugin.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Initialize the plugin
     *
     * @return void
     */
    abstract protected function init();

    /**
     * @param AbstractResponse $xResponse   The response
     *
     * @return static
     */
    public function initPlugin(AbstractResponse $xResponse): static
    {
        $this->xResponse = $xResponse;
        $this->init();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function response(): ?AbstractResponse
    {
        return $this->xResponse;
    }

    /**
     * Add a plugin command to the response
     *
     * @param string $sName    The command name
     * @param array|JsonSerializable $aOptions    The command options
     *
     * @return Command
     */
    public function addCommand(string $sName, array|JsonSerializable $aOptions): Command
    {
        return $this->xResponse
            ->addCommand($sName, $aOptions)
            ->setOption('plugin', $this->getName());
    }
}
