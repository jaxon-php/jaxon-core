<?php

namespace Jaxon\Di\Traits;

use Jaxon\Plugin\CallableMetadataInterface;
use ReflectionClass;

trait MetadataTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerMetadataReader()
    {
        // By default, register a fake metadata reader.
        $this->set('metadata_reader_null', function() {
            return new class implements CallableMetadataInterface
            {
                public function getAttributes(ReflectionClass|string $xReflectionClass,
                    array $aMethods = [], array $aProperties = []): array
                {
                    return [false, [], []];
                }
            };
        });
    }

    /**
     * Get the metadata reader with the given id
     *
     * @param string $sReaderId
     *
     * @return CallableMetadataInterface
     */
    public function getMetadataReader(string $sReaderId): CallableMetadataInterface
    {
        if(($sReaderId === 'attributes' || $sReaderId === 'annotations')
            && $this->h("metadata_reader_$sReaderId"))
        {
            return $this->g("metadata_reader_$sReaderId");
        }
        return $this->g('metadata_reader_null');
    }
}
