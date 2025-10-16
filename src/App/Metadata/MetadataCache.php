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

use Closure;

use function file_exists;
use function file_put_contents;
use function implode;
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
    private function filepath(string $sClass): string
    {
        $sFilename = str_replace(['\\', '.'], '_', strtolower($sClass));
        return "{$this->sCacheDir}/jaxon_metadata_{$sFilename}.php";
    }

    /**
     * Generate the PHP code to create a metadata object.
     *
     * @return array
     */
    private function encode(Metadata $xMetadata): array
    {
        $sMetadataVar = '$xMetadata';
        $sDataVar = '$xData';
        $aCalls = ["$sMetadataVar = new " . Metadata::class . '();'];
        foreach($xMetadata->getAttributes() as $sType => $aValues)
        {
            foreach($aValues as $sMethod => $xData)
            {
                $aCalls[] = "$sDataVar = {$sMetadataVar}->{$sType}('{$sMethod}');";
                foreach($xData->encode($sDataVar) as $sCall)
                {
                    $aCalls[] = $sCall;
                }
            }
        }
        $aCalls[] = "return $sMetadataVar;";
        return $aCalls;
    }

    /**
     * @param string $sClass
     * @param Metadata $xMetadata
     *
     * @return void
     */
    public function save(string $sClass, Metadata $xMetadata): void
    {
        $sDataCode = implode("\n    ", $this->encode($xMetadata));
        $sPhpCode = <<<CODE
<?php

return function() {
    $sDataCode
};

CODE;
        file_put_contents($this->filepath($sClass), $sPhpCode);
    }

    /**
     * @param string $sClass
     *
     * @return Metadata|null
     */
    public function read(string $sClass): ?Metadata
    {
        $sPath = $this->filepath($sClass);
        $fCreator = file_exists($sPath) ? require $sPath : null;
        return $fCreator instanceof Closure ? $fCreator() : null;
    }
}
