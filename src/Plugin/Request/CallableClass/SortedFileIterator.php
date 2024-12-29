<?php

/**
 * SortedIterator.php
 *
 * This class sorts files alphabetically by name.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Request\CallableClass;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplHeap;

use function strcmp;

class SortedFileIterator extends SplHeap
{
    /**
     * @param string $sDirectory
     */
    public function __construct(string $sDirectory)
    {
        $itFile = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sDirectory));
        foreach($itFile as $xFile)
        {
            $this->insert($xFile);
        }
    }

    /**
     * Compare elements in order to place them correctly in the heap
     *
     * @param mixed $xFile1
     * @param mixed $xFile2
     *
     * @return int
     */
    public function compare($xFile1, $xFile2): int
    {
        return strcmp($xFile2->getRealPath(), $xFile1->getRealPath());
    }
}
