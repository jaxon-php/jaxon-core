<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Metadata\InputDataInterface;
use Jaxon\App\Metadata\MetadataInterface;
use Jaxon\App\Metadata\MetadataReaderInterface;

trait MetadataTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerMetadataReader(): void
    {
        // By default, register a fake metadata reader.
        $this->set('metadata_reader_null', function() {
            return new class implements MetadataReaderInterface
            {
                public function getAttributes(InputDataInterface $xInputData): ?MetadataInterface
                {
                    return null;
                }
            };
        });
    }

    /**
     * Get the metadata reader with the given id
     *
     * @param string $sReaderId
     *
     * @return MetadataReaderInterface
     */
    public function getMetadataReader(string $sReaderId): MetadataReaderInterface
    {
        return $this->h("metadata_reader_$sReaderId") ?
            $this->g("metadata_reader_$sReaderId") :
            $this->g('metadata_reader_null');
    }
}
