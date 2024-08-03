<?php

namespace Jaxon\Request;

interface TargetInterface
{
    /**
     * The target method name.
     *
     * @return string
     */
    public function method(): string;

    /**
     * The target method args.
     *
     * @return array
     */
    public function args(): array;
}
