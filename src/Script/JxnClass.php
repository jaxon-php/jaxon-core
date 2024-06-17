<?php

namespace Jaxon\Script;

/**
 * JxnClass.php
 *
 * Call to a Jaxon registered class.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Dialog\DialogManager;
 
class JxnClass extends JxnCall
{
    /**
     * @var string
     */
    protected $sJsObject;

    /**
     * The class constructor
     *
     * @param DialogManager $xDialog
     * @param string $sJsObject
     */
    public function __construct(DialogManager $xDialog, string $sJsObject)
    {
        parent::__construct($xDialog, $sJsObject . '.');
        $this->sJsObject = $sJsObject;
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
