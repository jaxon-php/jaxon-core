<?php

namespace Jaxon\Di\Traits;

use Jaxon\App\Metadata\InputData;
use Jaxon\App\Metadata\Metadata;
use Jaxon\App\Metadata\MetadataCache;
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
        // Metadata cache
        $this->set(MetadataCache::class, fn($di) =>
            new MetadataCache($di->g('jaxon_metadata_cache_dir')));

        // By default, register a fake metadata reader.
        $this->set('metadata_reader_null', fn() => new class implements MetadataReaderInterface
        {
            public function getAttributes(InputData $xInputData): Metadata
            {
                return new Metadata();
            }
        });
    }

    /**
     * Get the metadata cache
     *
     * @return MetadataCache
     */
    public function getMetadataCache(): MetadataCache
    {
        return $this->g(MetadataCache::class);
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
