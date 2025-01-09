<?php

/**
 * LibraryRegistryInterface.php
 *
 * Functions for the library registry.
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog\Manager;

use Jaxon\App\Dialog\Library\AlertInterface;
use Jaxon\App\Dialog\Library\ConfirmInterface;
use Jaxon\App\Dialog\Library\ModalInterface;

interface LibraryRegistryInterface
{
    /**
     * Get the AlertInterface library
     *
     * @return AlertInterface
     */
    public function getAlertLibrary(): AlertInterface;

    /**
     * Get the ConfirmInterface library
     *
     * @return ConfirmInterface
     */
    public function getConfirmLibrary(): ConfirmInterface;

    /**
     * Get the ModalInterface library
     *
     * @return ModalInterface|null
     */
    public function getModalLibrary(): ?ModalInterface;
}
