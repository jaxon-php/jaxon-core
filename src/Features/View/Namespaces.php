<?php

/**
 * Namespaces.php - A trait for managing namespaces in view/template renderers.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Features\View;

trait Namespaces
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
     * Add a namespace to this template renderer
     *
     * @param string        $sNamespace         The namespace name
     * @param string        $sDirectory         The namespace directory
     * @param string        $sExtension         The extension to append to template names
     *
     * @return void
     */
    public function addNamespace($sNamespace, $sDirectory, $sExtension = '')
    {
        $this->aDirectories[$sNamespace] = ['path' => $sDirectory, 'ext' => $sExtension];
    }

    /**
     * Find the namespace of the template being rendered
     *
     * @param string        $sNamespace         The namespace name
     *
     * @return void
     */
    public function setCurrentNamespace($sNamespace)
    {
        $this->sDirectory = '';
        $this->sExtension = '';
        if(key_exists($sNamespace, $this->aDirectories))
        {
            // Make sure there's only one '/' at the end of the string
            $this->sDirectory = rtrim($this->aDirectories[$sNamespace]['path'], '/') . '/';
            // Make sure there's only one '.' at the beginning of the string
            $this->sExtension = '.' . ltrim($this->aDirectories[$sNamespace]['ext'], '.');
        }
    }
}
