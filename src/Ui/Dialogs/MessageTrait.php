<?php

/**
 * MessageTrait.php - Trait for alert messages.
 *
 * @package jaxon-dialogs
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Ui\Dialogs;

trait MessageTrait
{
    /**
     *
     *
     * @var bool
     */
    private $bReturn = false;

    /**
     * Set the library to return the javascript code or run it in the browser.
     *
     * @param bool $bReturn    Whether to return the code
     *
     * @return void
     */
    public function setReturn(bool $bReturn)
    {
        $this->bReturn = $bReturn;
    }

    /**
     * Check if the library should return the js code or run it in the browser.
     *
     * @return bool
     */
    public function getReturn(): bool
    {
        return $this->bReturn;
    }
}