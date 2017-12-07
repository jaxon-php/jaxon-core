<?php

/**
 * Sentry.php - Upload Trait
 *
 * The Jaxon class uses a modular plug-in system to facilitate the processing
 * of special Ajax requests made by a PHP page.
 * It generates Javascript that the page must include in order to make requests.
 * It handles the output of response commands (see <Jaxon\Response\Response>).
 * Many flags and settings can be adjusted to effect the behavior of the Jaxon class
 * as well as the client-side javascript.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2017 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Traits;

use Jaxon\Utils\Container;

trait Sentry
{
    /**
     * Get the Sentry instance
     *
     * @return \Jaxon\Sentry\Sentry
     */
    public function sentry()
    {
        return Container::getInstance()->getSentry();
    }

    /**
     * Get the Armada instance
     *
     * @return \Jaxon\Sentry\Traits\Armada
     */
    public function armada()
    {
        return Container::getInstance()->getArmada();
    }
}
