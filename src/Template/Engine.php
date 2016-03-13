<?php

namespace Xajax\Template;

use Tonic\Tonic;

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
        $this->sTemplatesDir = $sTemplatesDir;
        // Create the template engine instance
        Tonic::$root = $this->sTemplatesDir;
        $this->xEngine = new Tonic();
        if(is_writable($this->sTemplatesDir . '/cache'))
        {
            $this->xEngine->enable_content_cache = true;
            $this->xEngine->cache_dir = $this->sTemplatesDir . '/cache';
        }
    }

    public function render($sTemplate, array $aVars = array())
    {
        Tonic::setGlobals($aVars);
        return $this->xEngine->render($sTemplate);
    }
}
