<?php

/**
 * Message.php - Interface for alert messages.
 *
 * @package jaxon-dialogs
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Ui\Dialogs;

class Message implements \Jaxon\Contracts\Dialogs\Message
{
    use \Jaxon\Features\Dialogs\Message;

    /**
     * Print an alert message.
     *
     * @param string              $sMessage             The text of the message
     *
     * @return string|void
     */
    private function alert(string $sMessage)
    {
        if($this->getReturn())
        {
            return 'alert(' . $sMessage . ')';
        }
    }

    /**
     * Print a success message.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return string|void
     */
    public function success(string $sMessage, string $sTitle = '')
    {
        return $this->alert($sMessage);
    }

    /**
     * Print an information message.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return string|void
     */
    public function info(string $sMessage, string $sTitle = '')
    {
        return $this->alert($sMessage);
    }

    /**
     * Print a warning message.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return string|void
     */
    public function warning(string $sMessage, string $sTitle = '')
    {
        return $this->alert($sMessage);
    }

    /**
     * Print an error message.
     *
     * @param string              $sMessage             The text of the message
     * @param string              $sTitle               The title of the message
     *
     * @return string|void
     */
    public function error(string $sMessage, string $sTitle = '')
    {
        return $this->alert($sMessage);
    }
}
