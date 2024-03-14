<?php

/**
 * MessageTrait.php - Default methods for alert messages.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog\Library;

trait MessageTrait
{
    /**
     * Print an alert message.
     *
     * @param string $sContent The text of the message
     * @param string $sTitle The title of the message
     * @param string $sType The type of the message
     *
     * @return void
     */
    abstract protected function alert(string $sContent, string $sTitle, string $sType);

    /**
     * Show a success message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return void
     */
    public function success(string $sMessage, string $sTitle = '')
    {
        return $this->alert($sMessage, $sTitle, 'success');
    }

    /**
     * Show an information message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return void
     */
    public function info(string $sMessage, string $sTitle = '')
    {
        return $this->alert($sMessage, $sTitle, 'info');
    }

    /**
     * Show a warning message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return void
     */
    public function warning(string $sMessage, string $sTitle = '')
    {
        return $this->alert($sMessage, $sTitle, 'warning');
    }

    /**
     * Show an error message.
     *
     * @param string $sMessage    The text of the message
     * @param string $sTitle    The title of the message
     *
     * @return void
     */
    public function error(string $sMessage, string $sTitle = '')
    {
        return $this->alert($sMessage, $sTitle, 'error');
    }
}
