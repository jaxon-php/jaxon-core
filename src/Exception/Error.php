<?php

/**
 * Error.php - Xajax error
 *
 * This exception is thrown when a generic error occurs.
 *
 * @package xajax-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax\Exception;

use Exception;

class Error extends Exception
{
    public function __construct($sMessageKey, array $aPlaceHolders = array())
    {
        $sMessage = xajax_trans($sMessageKey, $aPlaceHolders);
        parent::__construct($sMessage);
    }
}
