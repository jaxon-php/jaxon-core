<?php

namespace Xajax\Template;

/*
	File: Engine.php

	Contains the Engine class.

	Title: Engine class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: Engine.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

class Engine
{
    protected $sTemplatesDir;
    protected $xEngine;

	public function __construct($sTemplatesDir)
    {
        $this->sTemplatesDir = trim($sTemplatesDir);
    	$this->xEngine = new \Latte\Engine;
    	$this->xEngine->setTempDirectory($this->sTemplatesDir . '/cache');
    }

    public function render($sTemplate, array $aVars = array())
    {
    	$sRendered = $this->xEngine->render($this->sTemplatesDir . '/' . $sTemplate, $aVars);
    	return $sRendered;
    }
}
