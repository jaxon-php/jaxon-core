<?php

namespace Jaxon\App\View;

interface ViewInterface
{
    /**
     * Add a namespace to the view renderer
     *
     * @param string $sNamespace    The namespace name
     * @param string $sDirectory    The namespace directory
     * @param string $sExtension    The extension to append to template names
     *
     * @return void
     */
    public function addNamespace(string $sNamespace, string $sDirectory, string $sExtension = '');

    /**
     * Render a view
     *
     * @param Store $store    A store populated with the view data
     *
     * @return string
     */
    public function render(Store $store): string;
}
