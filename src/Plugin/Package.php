<?php

namespace Jaxon\Plugin;

abstract class Package
{
    abstract public function config($sConfigFile);

    abstract public function css();

    abstract public function js();

    abstract public function ready();

    abstract public function html();
}
