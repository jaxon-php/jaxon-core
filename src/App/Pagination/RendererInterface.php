<?php

/**
 * RendererInterface.php
 *
 * Interface definition for the pagination renderer.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Pagination;

interface RendererInterface
{
    /**
     * Get the pagination HTML code
     *
     * @param Paginator $xPaginator
     *
     * @return string
     */
    public function getHtml(Paginator $xPaginator): string;
}
