<?php

/**
 * Paginator.php - Trait for pagination functions
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Traits;

use Jaxon\Utils\Container;

trait Paginator
{
    /**
     * Set the pagination renderer
     *
     * @param object        $xRenderer              The pagination renderer
     *
     * @return void
     */
    public function setPaginationRenderer($xRenderer)
    {
        Container::getInstance()->setPaginationRenderer($xRenderer);
    }

    /**
     * Get the pagination object for a Jaxon request
     *
     * @param integer                   $nItemsTotal        The total number of items
     * @param integer                   $nItemsPerPage      The number of items per page page
     * @param integer                   $nCurrentPage       The current page
     * @param Jaxon\Request\Request     $xRequest           A request to a Jaxon function
     *
     * @return Jaxon\Utils\Paginator     The paginator instance
     */
    public function paginator($nItemsTotal, $nItemsPerPage, $nCurrentPage, $xRequest)
    {
        $paginator = Container::getInstance()->getPaginator();
        $paginator->setup($nItemsTotal, $nItemsPerPage, $nCurrentPage, $xRequest);
        return $paginator;
    }
}
