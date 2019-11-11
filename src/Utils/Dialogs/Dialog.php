<?php

/**
 * Dialogs.php - Shows alert and confirm dialogs
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Dialogs;

use Jaxon\Contracts\Dialogs\Alert as AlertContract;
use Jaxon\Contracts\Dialogs\Confirm as ConfirmContract;

class Dialog
{
    /**
     * Javascript confirm function
     *
     * @var ConfirmContract
     */
    private $xConfirm;

    /**
     * Default javascript confirm function
     *
     * @var ConfirmContract
     */
    private $xDefaultConfirm;

    /**
     * Javascript alert function
     *
     * @var AlertContract
     */
    private $xAlert;

    /**
     * Default javascript alert function
     *
     * @var AlertContract
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
     * @param ConfirmContract         $xConfirm     The javascript confirm function
     *
     * @return void
     */
    public function setConfirm(ConfirmContract $xConfirm)
    {
        $this->xConfirm = $xConfirm;
    }

    /**
     * Get the javascript confirm function
     *
     * @return ConfirmContract
     */
    public function getConfirm()
    {
        return (($this->xConfirm) ? $this->xConfirm : $this->xDefaultConfirm);
    }

    /**
     * Get the default javascript confirm function
     *
     * @return ConfirmContract
     */
    public function getDefaultConfirm()
    {
        return $this->xDefaultConfirm;
    }

    /**
     * Set the javascript alert function
     *
     * @param AlertContract           $xAlert       The javascript alert function
     *
     * @return void
     */
    public function setAlert(AlertContract $xAlert)
    {
        $this->xAlert = $xAlert;
    }

    /**
     * Get the javascript alert function
     *
     * @return AlertContract
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
     * It is a function of the Confirm interface.
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
     * It is a function of the Alert interface.
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
     * It is a function of the Alert interface.
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
     * It is a function of the Alert interface.
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
     * It is a function of the Alert interface.
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
