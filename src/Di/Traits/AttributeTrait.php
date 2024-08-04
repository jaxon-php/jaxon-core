<?php

namespace Jaxon\Di\Traits;

use Jaxon\Plugin\CallableMetadataInterface;
use Jaxon\Plugin\Attribute\AttributeParser;
use Jaxon\Plugin\Attribute\AttributeReader;
use ReflectionClass;

use function sys_get_temp_dir;

trait AttributeTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerMetadataReaders()
    {
        $sCacheDirKey = 'jaxon_attributes_cache_dir';
        $this->val($sCacheDirKey, sys_get_temp_dir());
    
            // Attribute parser
        $this->set(AttributeParser::class, function($di) use($sCacheDirKey) {
            return new AttributeParser($di->g($sCacheDirKey));
        });

        // Attribute reader
        $this->set(AttributeReader::class, function($di) use($sCacheDirKey) {
            return new AttributeReader($di->g(AttributeParser::class), $di->g($sCacheDirKey));
        });
        $this->alias('metadata_reader_attributes', AttributeReader::class);

        // By default, register a fake annotation reader.
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
