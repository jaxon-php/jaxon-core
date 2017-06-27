<?php

/**
 * View.php - Trait for session functions
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Traits;

use Jaxon\Utils\Container;

trait Session
{
    /**
     * Get the session manager
     *
     * @return object        The session manager
     */
    public function getSessionManager()
    {
        return Container::getInstance()->getSessionManager();
    }
    
    /**
     * Set the session manager
     *
     * @param Closure               $xClosure           A closure to create the session instance
     *
     * @return void
     */
    public function setSessionManager($xClosure)
    {
        Container::getInstance()->setSessionManager($xClosure);
    }
}
