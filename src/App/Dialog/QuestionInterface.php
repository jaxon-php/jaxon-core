<?php

/**
 * QuestionInterface.php - A confirmation question for a Jaxon request
 *
 * Interface for adding a confirmation question which is asked before calling a Jaxon function.
 *
 * @package jaxon-core
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog;

interface QuestionInterface extends LibraryInterface
{
    /**
     * Add a confirm question to a function call.
     *
     * @param string $sQuestion
     * @param array $aArgs
     *
     * @return array
     */
    public function confirm(string $sQuestion, array $aArgs = []): array;
}
