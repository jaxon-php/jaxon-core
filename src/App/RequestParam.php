<?php

namespace Jaxon\App;

abstract class RequestParam
{
    /**
     * Convert a parameter value.
     *
     * @param mixed $value
     *
     * @return void
     */
    abstract public function set(mixed $value): void;
}
