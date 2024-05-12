<?php

/**
 * QuestionTrait.php - Show confirm question.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Dialog\Library;

use Jaxon\App\Dialog\QuestionInterface;

trait QuestionTrait
{
    /**
     * Get the QuestionInterface library
     *
     * @return QuestionInterface
     */
    abstract public function getQuestionLibrary(): QuestionInterface;

    /**
     * @param string $sStr
     * @param array $aArgs
     *
     * @return array
     */
    abstract private function phrase(string $sStr, array $aArgs = []): array;

    /**
     * Add a confirm question to a function call.
     *
     * @param string $sQuestion
     * @param array $aArgs
     *
     * @return array
     */
    public function confirm(string $sQuestion, array $aArgs = []): array
    {
        return [
            'lib' => $this->getQuestionLibrary()->getName(),
            'phrase' => $this->phrase($sQuestion, $aArgs),
        ];
    }
}
