<?php

namespace Jaxon\App\View;

use function ltrim;
use function rtrim;

trait ViewTrait
{
    /**
     * The template directories
     *
     * @var array
     */
    protected $aDirectories = [];

    /**
     * The directory of the current template
     *
     * @var string
     */
    protected $sDirectory = '';

    /**
     * The extension of the current template
     *
     * @var string
     */
    protected $sExtension = '';

    /**
     * Add a namespace to the view renderer
     *
     * @param string $sNamespace    The namespace name
     * @param string $sDirectory    The namespace directory
     * @param string $sExtension    The extension to append to template names
     *
     * @return void
     */
    public function addNamespace(string $sNamespace, string $sDirectory, string $sExtension = '')
    {
        $this->aDirectories[$sNamespace] = ['path' => $sDirectory, 'ext' => $sExtension];
    }

    /**
     * Set the namespace of the template being rendered
     *
     * @param string $sNamespace    The namespace name
     *
     * @return void
     */
    public function setCurrentNamespace(string $sNamespace)
    {
        $this->sDirectory = '';
        $this->sExtension = '';
        if(isset($this->aDirectories[$sNamespace]))
        {
            // Make sure there's only one '/' at the end of the string
            $this->sDirectory = rtrim($this->aDirectories[$sNamespace]['path'], '/') . '/';
            // Make sure there's only one '.' at the beginning of the string
            $this->sExtension = '.' . ltrim($this->aDirectories[$sNamespace]['ext'], '.');
        }
    }
}
