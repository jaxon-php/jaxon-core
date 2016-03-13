<?php

namespace Xajax\Response;

/*
	File: CustomResponse.php

	Contains the custom response class.
	
	Title: xajax custom response class
	
	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: xajaxResponse.php 361 2007-05-24 12:48:14Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

class CustomResponse
{
	protected $sOutput;
	protected $sContentType;
	
	protected $sCharacterEncoding;
	protected $bOutputEntities;
	
	public function __construct($sContentType)
	{
		$this->sOutput = '';
		$this->sContentType = $sContentType;
		
		$objResponseManager = Manager::getInstance();
		
		$this->sCharacterEncoding = $objResponseManager->getCharacterEncoding();
		$this->bOutputEntities = $objResponseManager->getOutputEntities();
	}
	
	public function setCharacterEncoding($sCharacterEncoding)
	{
		$this->sCharacterEncoding = $sCharacterEncoding;
	}
	
	public function setOutputEntities($bOutputEntities)
	{
		$this->bOutputEntities = $bOutputEntities;
	}
	
	public function clear()
	{
		$this->sOutput = '';
	}
	
	public function append($sOutput)
	{
		$this->sOutput .= $sOutput;
	}
	
	public function appendResponse($objResponse)
	{
		//SkipDebug
		if(!($objResponse instanceof CustomResponse ))
		{
			throw new \Xajax\Exception\Error('errors.mismatch.types', array('class' => get_class($objResponse)));
		}
		
		if($objResponse->getContentType() != $this->getContentType())
		{
			throw new \Xajax\Exception\Error('errors.mismatch.content-types', array('type' => $objResponse->getContentType()));
		}
		
		if($objResponse->getCharacterEncoding() != $this->getCharacterEncoding())
		{
			throw new \Xajax\Exception\Error('errors.mismatch.encodings', array('encoding' => $objResponse->getCharacterEncoding()));
		}
		
		if($objResponse->getOutputEntities() != $this->getOutputEntities())
		{
			throw new \Xajax\Exception\Error('errors.mismatch.entities', array('entities' => $objResponse->getOutputEntities()));
		}
		//EndSkipDebug
		
		$this->sOutput .= $objResponse->getOutput();
	}
	
	public function getContentType()
	{
		return $this->sContentType;
	}
	
	public function getCharacterEncoding()
	{
		return $this->sCharacterEncoding;
	}
	
	public function getOutputEntities()
	{
		return $this->bOutputEntities;
	}
	
	public function getOutput()
	{
		return $this->sOutput;
	}
	
	public function printOutput()
	{
		$sContentType = $this->sContentType;
		$sCharacterSet = $this->sCharacterEncoding;
		
		header("content-type: {$sContentType}; charset={$sCharacterSet}");
		
		echo $this->sOutput;
	}
}
