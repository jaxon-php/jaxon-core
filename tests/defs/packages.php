<?php

use Jaxon\Plugin\Package;

class SamplePackage extends Package
{
    public static function config(): array
    {
        return [];
    }

    public function getHtml(): string
    {
        return '';
    }
}

class BadConfigPackage extends Package
{
    public static function config()
    {
        return true; // This is wrong. The return value must be a string or an array.
    }

    public function getHtml(): string
    {
        return '';
    }
}
