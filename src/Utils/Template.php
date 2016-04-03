<?php

namespace Xajax\Utils;

/*
	File: Template.php

	Contains the Template class.

	Title: Template class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: Template.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

class Template
{
    protected $sTemplateDir;
    protected $xEngine;

	/*
		Object: xInstance
		The only instance of the Template (Singleton)
	*/
	private static $xInstance = null;

	/*
		Function: getInstance
		
		Implementation of the singleton pattern: returns the one and only instance of the Template
		
		Returns:
		
		object : a reference to the Template object.
	*/
	public static function getInstance()
	{
		if(!self::$xInstance)
		{
			self::$xInstance = new Template();    
		}
		return self::$xInstance;
	}

	private function __construct()
    {
    	$this->xEngine = new \Latte\Engine;
    }

	public function setTemplateDir($sTemplateDir)
    {
        $this->sTemplateDir = trim($sTemplateDir);
    	$this->xEngine->setTempDirectory($this->sTemplateDir . '/cache');
    }

    public function render($sTemplate, array $aVars = array())
    {
    	$sRendered = $this->xEngine->renderToString($this->sTemplateDir . '/' . $sTemplate, $aVars);
    	return $sRendered;
    }
}
