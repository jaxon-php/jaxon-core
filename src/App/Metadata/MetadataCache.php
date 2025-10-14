<?php

/**
 * Cache.php
 *
 * Cache for callable class metadata.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata;

use function is_callable;
use function str_replace;
use function strtolower;

class MetadataCache
{
    /**
     * @param string $sCacheDir
     */
    public function __construct(private string $sCacheDir)
    {}

    /**
     * @param string $sClass
     *
     * @return string
     */
    private function filename(string $sClass): string
    {
        $sName = str_replace(['\\', '.'], '_', strtolower($sClass));
        return "{$this->sCacheDir}/{$sName}.php";
    }

    /**
     * @param string $sClass
     * @param Metadata $xMetadata
     *
     * @return void
     */
    public function save(string $sClass, Metadata $xMetadata): void
    {
        $sFilename = $this->filename($sClass);
        $sDataCode = implode("\n    ", $xMetadata->encode());
        $sPhpCode = <<<CODE
<?php

return function() {
    $sDataCode
};

CODE;
        file_put_contents($sFilename, $sPhpCode);
    }

    /**
     * @param string $sClass
     *
     * @return Metadata|null
     */
    public function read(string $sClass): ?Metadata
    {
        $fFunction = require $this->filename($sClass);
        return !is_callable($fFunction) ? null : $fFunction();
    }
}
