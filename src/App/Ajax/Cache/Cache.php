<?php

namespace Jaxon\App\Ajax\Cache;

use function is_callable;

class Cache
{
    /**
     * @var array
     */
    private array $values = [];

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->values[$key] ?? $default;
        if(is_callable($value))
        {
            $value = $value();
            $this->values[$key] = $value;
        }

        return $value;
    }
}
