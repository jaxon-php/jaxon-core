<?php

/**
 * Factory.php - Trait for Jaxon Request Factory
 *
 * Make functions of the Jaxon Request Factory class available to Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Traits;

trait Factory
{
    /**
     * Return the javascript call to an Jaxon object method
     *
     * @param string         $sMethod           The method (without class) name
     * @param ...            $xParams           The parameters of the method
     *
     * @return object
     */
    public function call($sMethod)
    {
        $aArgs = func_get_args();
        // Make the request
        return call_user_func_array([rq(get_class()), 'call'], $aArgs);
    }

    /**
     * Make the pagination links for a registered Jaxon class method
     *
     * @param integer $nItemsTotal the total number of items
     * @param integer $nItemsPerPage the number of items per page
     * @param integer $nCurrentPage the current page
     * @param string  $sMethod the name of the method
     * @param ... $parameters the parameters of the method
     *
     * @return string the pagination links
     */
    public function paginate($nItemsTotal, $nItemsPerPage, $nCurrentPage, $sMethod)
    {
        $aArgs = func_get_args();
        // Make the request
        return call_user_func_array([rq(get_class()), 'paginate'], $aArgs);
    }
}
