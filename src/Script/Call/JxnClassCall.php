<?php

namespace Jaxon\Script\Call;

/**
 * JxnClassCall.php
 *
 * Call to a registered class.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

class JxnClassCall extends JxnCall
{
    /**
     * The class constructor
     *
     * @param string $sJsObject
     */
    public function __construct(protected string $sJsObject)
    {
        parent::__construct("$sJsObject.");
    }

    /**
     * Get the js class name
     *
     * @return string
     */
    public function _class(): string
    {
        return $this->sJsObject;
    }
}
