<?php

namespace Jaxon\Plugin\Code;

interface MinifierInterface
{
    /**
     * Minify javascript code
     *
     * @param string $sJsFile The javascript file to be minified
     * @param string $sMinFile The minified javascript file
     *
     * @return bool
     */
    public function minify(string $sJsFile, string $sMinFile): bool;
}
