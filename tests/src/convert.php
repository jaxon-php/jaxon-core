<?php

use Jaxon\App\RequestParam;

class IntParam extends RequestParam
{
    private int $value;

    public function set(mixed $value): void
    {
        $this->value = 2 * (int)$value;
    }

    public function get(): int
    {
        return $this->value;
    }
}

class StringParam extends RequestParam
{
    private string $value;

    public function set(mixed $value): void
    {
        $this->value = strtoupper((string)$value);
    }

    public function get(): string
    {
        return $this->value;
    }
}

class Convert
{
    public function convert(string $first, IntParam $second, StringParam $third)
    {
        $xResponse = jaxon()->getResponse();
        $int = $second->get();
        $str = $third->get();
        $xResponse->alert("$first is not changed, second is $int and third is $str.");
    }
}

function convert(string $first, IntParam $second, StringParam $third)
{
    $xResponse = jaxon()->getResponse();
    $int = $second->get();
    $str = $third->get();
    $xResponse->alert("$first is not changed, second is $int and third is $str.");
}
