<?php

/**
 * ResponsePlugin.php
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

use Jaxon\Response\Response;

abstract class ResponsePlugin extends Plugin implements ResponsePluginInterface
{
    /**
     * The object used to build the response that will be sent to the client browser
     *
     * @var Response
     */
    protected $xResponse = null;

    /**
     * @inheritDoc
     */
    public function setResponse(Response $xResponse)
    {
        $this->xResponse = $xResponse;
    }

    /**
     * @inheritDoc
     */
    public function response(): ?Response
    {
        return $this->xResponse;
    }

    /**
     * Add a client side plugin command to the response object
     *
     * Used internally to add a command to the response command list.
     * This will call <Jaxon\Response\Response->addPluginCommand> using the
     * reference provided in <Jaxon\Response\Response->setResponse>.
     *
     * @param array $aAttributes    The attributes of the command
     * @param mixed $xData    The data to be added to the command
     *
     * @return void
     */
    public function addCommand(array $aAttributes, $xData)
    {
        $this->xResponse->addPluginCommand($this, $aAttributes, $xData);
    }
}
