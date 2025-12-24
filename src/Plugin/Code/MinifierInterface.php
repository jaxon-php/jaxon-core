<?php

namespace Jaxon\Plugin\Code;

interface MinifierInterface
{
    /**
     * Minify javascript code
     *
     * @param string $sCode The javascript code to be minified
     *
     * @return string|false
     */
    public function minifyJsCode(string $sCode): string|false;

    /**
     * Minify css code
     *
     * @param string $sCode The css code to be minified
     *
     * @return string|false
     */
    public function minifyCssCode(string $sCode): string|false;
}
