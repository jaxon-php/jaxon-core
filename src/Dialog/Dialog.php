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
     * @var Jaxon\Dialog\Interfaces\Confirm
     */
    private $xConfirm;

    /**
     * Default javascript confirm function
     *
     * @var Jaxon\Dialog\Confirm
     */
    private $xDefaultConfirm;

    /**
     * Javascript alert function
     *
     * @var Jaxon\Dialog\Interfaces\Alert
     */
    private $xAlert;

    /**
     * Default javascript alert function
     *
     * @var Jaxon\Dialog\Alert
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
     * @param Jaxon\Dialog\Interfaces\Confirm         $xConfirm     The javascript confirm function
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
     * @return Jaxon\Dialog\Interfaces\Confirm
     */
    public function getConfirm()
    {
        return (($this->xConfirm) ? $this->xConfirm : $this->xDefaultConfirm);
    }

    /**
     * Get the default javascript confirm function
     *
     * @return Jaxon\Dialog\Confirm
     */
    public function getDefaultConfirm()
    {
        return $this->xDefaultConfirm;
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
     * Set the javascript alert function
     *
     * @param Jaxon\Dialog\Interfaces\Alert           $xAlert       The javascript alert function
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
     * @return Jaxon\Dialog\Interfaces\Alert
     */
    public function getAlert()
    {
        return (($this->xAlert) ? $this->xAlert : $this->xDefaultAlert);
    }

    /**
     * Get the default javascript alert function
     *
     * @return Jaxon\Dialog\Alert
     */
    public function getDefaultAlert()
    {
        return $this->xDefaultAlert;
    }
}
