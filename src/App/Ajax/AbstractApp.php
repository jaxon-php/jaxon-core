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

abstract class AbstractApp implements AppInterface
{
    use AppTrait;

    /**
     * The class constructor
     */
    public function __construct()
    {
        $this->initApp();
    }
}
