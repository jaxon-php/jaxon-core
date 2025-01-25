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
     * Render an array of pagination links
     *
     * @param Page[] $aPages
     * @param Page $xPrevPage
     * @param Page $xNextPage
     *
     * @return string
     */
    public function render(array $aPages, Page $xPrevPage, Page $xNextPage): string;
}
