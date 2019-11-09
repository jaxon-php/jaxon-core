<?php

/**
 * Paginator.php - Jaxon Pagination Factory
 *
 * Create pagination links to a given class.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Factory\CallableClass;

use Jaxon\Request\Support\CallableObject;

class Paginator
{
    /**
     * The callable object this factory is attached to
     *
     * @var CallableObject
     */
    private $xCallable;

    /**
     * The total number of items
     *
     * @var integer
     */
    private $nItemsTotal = 0;

    /**
     * The number of items per page
     *
     * @var integer
     */
    private $nItemsPerPage = 0;

    /**
     * The current page
     *
     * @var integer
     */
    private $nCurrentPage = 0;

    /**
     * The class constructor
     *
     * @param CallableObject        $xCallable
     */
    public function __construct(CallableObject $xCallable)
    {
        $this->xCallable = $xCallable;
    }

    /**
     * Set the paginator properties
     *
     * @param integer $nItemsTotal the total number of items
     * @param integer $nItemsPerPage the number of items per page
     * @param integer $nCurrentPage the current page
     *
     * @return Paginator
     */
    public function setProperties($nItemsTotal, $nItemsPerPage, $nCurrentPage)
    {
        $this->nItemsTotal = $nItemsTotal;
        $this->nItemsPerPage = $nItemsPerPage;
        $this->nCurrentPage = $nCurrentPage;

        return $this;
    }

    /**
     * Generate the corresponding javascript code for a call to any method
     *
     * @return string
     */
    public function __call($sMethod, $aArguments)
    {
        // Add the paginator options to the method arguments
        $aPgArgs = [$this->nItemsTotal, $this->nItemsPerPage, $this->nCurrentPage, $sMethod];
        $aArguments = array_merge($aPgArgs, $aArguments);

        // Make the request
        $factory = rq()->setCallable($this->xCallable);
        return call_user_func_array([$factory, 'paginate'], $aArguments);
    }
}
