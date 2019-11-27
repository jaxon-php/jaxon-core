<?php

/**
 * Validator.php - Trait for validation functions
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Features;

trait Validator
{
    /**
     * Get the last error message
     *
     * @return string          The last error message
     */
    public function getValidatorMessage()
    {
        return jaxon()->di()->getValidator()->getErrorMessage();
    }

    /**
     * Validate a function name
     *
     * @param string        $sName            The function name
     *
     * @return boolean            True if the function name is valid, and false if not
     */
    public function validateFunction($sName)
    {
        return jaxon()->di()->getValidator()->validateFunction($sName);
    }

    /**
     * Validate an event name
     *
     * @param string        $sName            The event name
     *
     * @return boolean            True if the event name is valid, and false if not
     */
    public function validateEvent($sName)
    {
        return jaxon()->di()->getValidator()->validateEvent($sName);
    }

    /**
     * Validate a class name
     *
     * @param string        $sName            The class name
     *
     * @return boolean            True if the class name is valid, and false if not
     */
    public function validateClass($sName)
    {
        return jaxon()->di()->getValidator()->validateClass($sName);
    }

    /**
     * Validate a method name
     *
     * @param string        $sName            The function name
     *
     * @return boolean            True if the method name is valid, and false if not
     */
    public function validateMethod($sName)
    {
        return jaxon()->di()->getValidator()->validateMethod($sName);
    }

    /**
     * Validate an uploaded file
     *
     * @param string        $sName            The uploaded file variable name
     * @param array         $aUploadedFile    The file data received in the $_FILES array
     *
     * @return boolean            True if the file data are valid, and false if not
     */
    public function validateUploadedFile($sName, array $aUploadedFile)
    {
        return jaxon()->di()->getValidator()->validateUploadedFile($sName, $aUploadedFile);
    }
}
