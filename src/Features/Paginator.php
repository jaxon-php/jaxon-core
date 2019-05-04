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

namespace Jaxon\Features;

use Jaxon\DI\Container;
use Jaxon\Request\Request;

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
     * Set a new directory for pagination templates
     *
     * @param string        $sDirectory             The directory path
     *
     * @return void
     */
    public function setPaginationDir($sDirectory)
    {
        return Container::getInstance()->getTemplate()->setPaginationDir($sDirectory);
    }
}
