<?php

/**
 * Alert.php - Interface for alert messages.
 *
 * @package jaxon-dialogs
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Dialog;

class Alert implements Interfaces\Alert
{
    use \Jaxon\Features\Dialog\Alert;

    /**
     * Print an alert message.
     *
     * @param string              $message              The text of the message
     *
     * @return string|void
     */
    protected function alert($message)
    {
        if($this->getReturn())
        {
            return 'alert(' . $message . ')';
        }
    }

    /**
     * Print a success message.
     *
     * It is a function of the Jaxon\Dialog\Interfaces\Alert interface.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return string|void
     */
    public function success($message, $title = null)
    {
        return $this->alert($message);
    }

    /**
     * Print an information message.
     *
     * It is a function of the Jaxon\Dialog\Interfaces\Alert interface.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return string|void
     */
    public function info($message, $title = null)
    {
        return $this->alert($message);
    }

    /**
     * Print a warning message.
     *
     * It is a function of the Jaxon\Dialog\Interfaces\Alert interface.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return string|void
     */
    public function warning($message, $title = null)
    {
        return $this->alert($message);
    }

    /**
     * Print an error message.
     *
     * It is a function of the Jaxon\Dialog\Interfaces\Alert interface.
     *
     * @param string              $message              The text of the message
     * @param string|null         $title                The title of the message
     *
     * @return string|void
     */
    public function error($message, $title = null)
    {
        return $this->alert($message);
    }
}
