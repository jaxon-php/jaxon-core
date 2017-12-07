<?php

/**
 * Template.php - Template engine
 *
 * Generate templates with template vars.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Template;

class Template
{
    protected $aNamespaces;

    public function __construct($sTemplateDir)
    {
        $sTemplateDir = rtrim(trim($sTemplateDir), "/\\");
        $this->aNamespaces = [
            'jaxon' => [
                'directory' => $sTemplateDir . DIRECTORY_SEPARATOR,
                'extension' => '.php',
            ],
            'pagination' => [
                'directory' => $sTemplateDir . DIRECTORY_SEPARATOR . 'pagination' . DIRECTORY_SEPARATOR,
                'extension' => '.php',
            ],
        ];
    }

    /**
     * Add a namespace to the template system
     *
     * @param string        $sNamespace         The namespace name
     * @param string        $sDirectory         The namespace directory
     * @param string        $sExtension         The extension to append to template names
     *
     * @return void
     */
    public function addNamespace($sNamespace, $sDirectory, $sExtension = '')
    {
        // The 'jaxon' key cannot be overriden
        if($sNamespace == 'jaxon')
        {
            return;
        }
        // Save the namespace
        $this->aNamespaces[$sNamespace] = [
            'directory' => rtrim(trim($sDirectory), "/\\") . DIRECTORY_SEPARATOR,
            'extension' => $sExtension,
        ];
    }

    /**
     * Set a new directory for pagination templates
     *
     * @param string        $sDirectory             The directory path
     *
     * @return void
     */
    public function setPaginationDir($sDirectory)
    {
        $this->addNamespace('pagination', $sDirectory, '.php');
    }

    /**
     * Set a cache directory for the template engine
     *
     * @param string        $sCacheDir            The cache directory
     *
     * @return void
     */
    public function setCacheDir($sCacheDir)
    {
        // Nothing to do
    }

    /**
     * Render a template
     *
     * @param string        $sTemplate            The name of template to be rendered
     * @param string        $aVars                The template vars
     *
     * @return string        The template content
     */
    public function render($sTemplate, array $aVars = array())
    {
        $sTemplate = trim($sTemplate);
        // Get the namespace name
        $sNamespace = '';
        $iSeparatorPosition = strrpos($sTemplate, '::');
        if($iSeparatorPosition !== false)
        {
            $sNamespace = substr($sTemplate, 0, $iSeparatorPosition);
            $sTemplate = substr($sTemplate, $iSeparatorPosition + 2);
        }
        // The default namespace is 'jaxon'
        if(!($sNamespace = trim($sNamespace)))
        {
            $sNamespace = 'jaxon';
        }
        // Check if the namespace is defined
        if(!key_exists($sNamespace, $this->aNamespaces))
        {
            return false;
        }
        $aNamespace = $this->aNamespaces[$sNamespace];
        // Get the template path
        $sTemplatePath = $aNamespace['directory'] . $sTemplate . $aNamespace['extension'];
        // Render the template
        $xRenderer = new Renderer();
        return $xRenderer->render($sTemplatePath, $aVars);
    }
}
