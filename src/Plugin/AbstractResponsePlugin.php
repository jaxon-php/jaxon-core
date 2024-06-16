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

use Jaxon\Script\JqCall;
use Jaxon\Plugin\Response\DataBag\DataBagContext;
use Jaxon\Response\AbstractResponse;
use JsonSerializable;

/**
 * @method JqCall jq(string $sPath = '', $xContext = null)
 * @method DataBagContext bag(string $sName)
 */
abstract class AbstractResponsePlugin extends AbstractPlugin implements ResponsePluginInterface
{
    /**
     * The object used to build the response that will be sent to the client browser
     *
     * @var AbstractResponse
     */
    protected $xResponse = null;

    /**
     * @inheritDoc
     */
    public function setResponse(AbstractResponse $xResponse)
    {
        $this->xResponse = $xResponse;
    }

    /**
     * @inheritDoc
     */
    public function response(): ?AbstractResponse
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
     * @param string $sName    The command name
     * @param array|JsonSerializable $aOptions    The command options
     *
     * @return void
     */
    public function addCommand(string $sName, array|JsonSerializable $aOptions)
    {
        $this->xResponse->addCommand($sName, $aOptions);
        $this->xResponse->setOption('plugin', $this->getName());
    }
}
