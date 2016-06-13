<?php

/**
 * Error.php - Jaxon error
 *
 * This exception is thrown when a generic error occurs.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Exception;

use Exception;

class Error extends Exception
{
    public function __construct($sMessageKey, array $aPlaceHolders = array())
    {
        $sMessage = jaxon_trans($sMessageKey, $aPlaceHolders);
        parent::__construct($sMessage);
    }
}
