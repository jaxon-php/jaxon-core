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

abstract class AbstractResponsePlugin extends AbstractPlugin implements ResponsePluginInterface
{
    use ResponsePluginTrait;

    /**
     * Initialize the plugin
     *
     * @return void
     */
    protected function init(): void
    {}
}
