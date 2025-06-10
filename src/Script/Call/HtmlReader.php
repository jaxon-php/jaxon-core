<?php

/**
 * HtmlReader.php
 *
 * Helpers functions to read values from web pages or forms.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Script\Call;

class HtmlReader
{
    public function __construct(private string $sElementId)
    {}

    /**
     * @return array
     */
    public function form(): array
    {
        return ['_type' => 'form', '_name' => $this->sElementId];
    }

    /**
     * @return array
     */
    public function input(): array
    {
        return ['_type' => 'input', '_name' => $this->sElementId];
    }

    /**
     * @return array
     */
    public function checked(): array
    {
        return ['_type' => 'checked', '_name' => $this->sElementId];
    }

    /**
     * @return array
     */
    public function select(): array
    {
        return $this->input();
    }

    /**
     * @return array
     */
    public function html(): array
    {
        return ['_type' => 'html', '_name' => $this->sElementId];
    }

    /**
     * @return array
     */
    public function page(): array
    {
        return ['_type' => 'page', '_name' => ''];
    }
}
