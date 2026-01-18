<?php

/**
 * NoLibrary.php
 *
 * Used to indicate that no dialog library is defined.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2026 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-dialogs
 */

namespace Jaxon\App\Dialog\Library;

class NoDialogLibrary implements LibraryInterface, AlertInterface, ConfirmInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'undefined';
    }

    /**
     * @inheritDoc
     */
    public function getCssUrls(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getCssCode(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getJsUrls(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getJsCode(): string
    {
        return '';
    }
}
