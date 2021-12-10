<?php

/**
 * Engine.php - Template engine
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

class Engine
{
    /**
     * The namespaces
     *
     * @var array   $aNamespaces
     */
    protected $aNamespaces;

    /**
     * The constructor
     *
     * @param   string      $sTemplateDir       The template directory
     */
    public function __construct($sTemplateDir)
    {
        $sTemplateDir = rtrim(trim($sTemplateDir), '/\\');
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
        if($sNamespace == 'jaxon' || $sNamespace == 'pagination')
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
    public function pagination($sDirectory)
    {
        $this->aNamespaces['pagination']['directory'] = rtrim(trim($sDirectory), "/\\") . DIRECTORY_SEPARATOR;
    }

    /**
     * Render a template
     *
     * @param string        $sPath                The path to the template
     * @param array         $aVars                The template vars
     *
     * @return string
     */
    private function _render($sPath, array $aVars)
    {
        // Make the template vars available, throught a Context object.
        $xContext = new Context($this);
        foreach($aVars as $sName => $xValue)
        {
            $sName = (string)$sName;
            $xContext->$sName = $xValue;
        }
        // Render the template
        $cRenderer = function($_sPath) {
            ob_start();
            include($_sPath);
            return ob_get_clean();
        };
        // Call the closure in the context of the $xContext object.
        // So the keyword '$this' in the template will refer to the $xContext object.
        return \call_user_func($cRenderer->bindTo($xContext), $sPath);
    }

    /**
     * Render a template
     *
     * @param string        $sTemplate            The name of template to be rendered
     * @param array         $aVars                The template vars
     *
     * @return string
     */
    public function render($sTemplate, array $aVars = [])
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
            return '';
        }
        $aNamespace = $this->aNamespaces[$sNamespace];
        // Get the template path
        $sTemplatePath = $aNamespace['directory'] . $sTemplate . $aNamespace['extension'];
        // Render the template
        return $this->_render($sTemplatePath, $aVars);
    }
}
