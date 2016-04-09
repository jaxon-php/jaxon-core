<?php

namespace Xajax\Utils;

class Template
{
    protected $sTemplateDir;
    protected $xEngine;

	public function __construct($sTemplateDir)
    {
    	$this->xEngine = new \Latte\Engine;
        $this->sTemplateDir = trim($sTemplateDir);
    	$this->xEngine->setTempDirectory($this->sTemplateDir . '/cache');
    }

	/**
	 * Render a template
	 *
	 * @param string		$sTemplate			The name of template to be rendered
	 * @param string		$aVars				The template vars
	 *
	 * @return string		The template content
	 */
    public function render($sTemplate, array $aVars = array())
    {
    	$sRendered = $this->xEngine->renderToString($this->sTemplateDir . '/' . $sTemplate, $aVars);
    	return $sRendered;
    }
}
