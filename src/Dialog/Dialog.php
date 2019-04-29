<?php

/**
 * Dialogs.php - Shows alert and confirm dialogs
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Dialog;

use Jaxon\Jaxon;
use Jaxon\Plugin\Package;
use Jaxon\Config\Config;

class Dialog
{
    /**
     * Javascript confirm function
     *
     * @var Interfaces\Confirm
     */
    private $xConfirm;

    /**
     * Default javascript confirm function
     *
     * @var Confirm
     */
    private $xDefaultConfirm;

    /**
     * Javascript alert function
     *
     * @var Interfaces\Alert
     */
    private $xAlert;

    /**
     * Default javascript alert function
     *
     * @var Alert
     */
    private $xDefaultAlert;

    /**
     * The constructor
     */
    public function __construct()
    {
        // Javascript confirm function
        $this->xConfirm = null;
        $this->xDefaultConfirm = new Confirm();

        // Javascript alert function
        $this->xAlert = null;
        $this->xDefaultAlert = new Alert();
    }

    /**
     * Set the javascript confirm function
     *
     * @param Interfaces\Confirm         $xConfirm     The javascript confirm function
     *
     * @return void
     */
    public function setConfirm(Interfaces\Confirm $xConfirm)
    {
        $this->xConfirm = $xConfirm;
    }

    /**
     * Get the javascript confirm function
     *
     * @return Interfaces\Confirm
     */
    public function getConfirm()
    {
        return (($this->xConfirm) ? $this->xConfirm : $this->xDefaultConfirm);
    }

    /**
     * Get the default javascript confirm function
     *
     * @return Confirm
     */
    public function getDefaultConfirm()
    {
        return $this->xDefaultConfirm;
    }

    /**
     * Set the javascript alert function
     *
     * @param Interfaces\Alert           $xAlert       The javascript alert function
     *
     * @return void
     */
    public function setAlert(Interfaces\Alert $xAlert)
    {
        $this->xAlert = $xAlert;
    }

    /**
     * Get the javascript alert function
     *
     * @return Interfaces\Alert
     */
    public function getAlert()
    {
        return (($this->xAlert) ? $this->xAlert : $this->xDefaultAlert);
    }

    /**
     * Get the default javascript alert function
     *
     * @return Alert
     */
    public function getDefaultAlert()
    {
        return $this->xDefaultAlert;
    }

    /**
     * Get the script which makes a call only if the user answers yes to the given question
     *
     * @return string
     */
    public function confirm($question, $yesScript, $noScript)
    {
        return $this->getConfirm()->confirm($question, $yesScript, $noScript);
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
        return $this->getAlert()->success($message, $title);
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
        return $this->getAlert()->info($message, $title);
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
        return $this->getAlert()->warning($message, $title);
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
        return $this->getAlert()->error($message, $title);
    }
}
