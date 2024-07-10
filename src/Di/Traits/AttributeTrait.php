<?php

namespace Jaxon\Di\Traits;

use Jaxon\Plugin\Attribute\AttributeParser;
use Jaxon\Plugin\Attribute\AttributeReader;

trait AttributeTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerAttributes()
    {
        // Attribute parser
        $this->set(AttributeParser::class, function($di) {
            return new AttributeParser($di->g('jaxon_attributes_cache_dir'));
        });

        // Attribute reader
        $this->set(AttributeReader::class, function($di) {
            return new AttributeReader($di->g(AttributeParser::class), $di->g('jaxon_attributes_cache_dir'));
        });
    }
}
