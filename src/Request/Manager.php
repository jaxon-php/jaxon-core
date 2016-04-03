<?php

namespace Xajax\Request;

use Xajax\Utils\TemplateTrait;
use Xajax\Utils\ConfigTrait;

/*

	File: Manager.php

	Contains the Manager class

	Title: Manager class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: Manager.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

if(!defined('XAJAX_METHOD_UNKNOWN')) define('XAJAX_METHOD_UNKNOWN', 0);
if(!defined('XAJAX_METHOD_GET')) define('XAJAX_METHOD_GET', 1);
if(!defined('XAJAX_METHOD_POST')) define('XAJAX_METHOD_POST', 2);

/*
	Class: Manager
	
	This class processes the input arguments from the GET or POST data of 
	the request.  If this is a request for the initial page load, no arguments
	will be processed.  During a xajax request, any arguments found in the
	GET or POST will be converted to a PHP array.
*/
class Manager
{
	use TemplateTrait;
	use ConfigTrait;

	/*
		Array: aArgs
		
		An array of arguments received via the GET or POST parameter
		xjxargs.
	*/
	private $aArgs;
	
	/*
		Integer: nMethod
		
		Stores the method that was used to send the arguments from the client.  Will
		be one of: XAJAX_METHOD_UNKNOWN, XAJAX_METHOD_GET, XAJAX_METHOD_POST
	*/
	private $nMethod;
	
	/*
		Array: aSequence
		
		Stores the decoding sequence table.
	*/
	private $aSequence;
	
	/*
		Function: __convertStringToBool
		
		Converts a string to a bool var.
		
		Parameters:
			$sValue - (string): 
				
		Returns:
			(bool) : true / false
	
	*/
	
	private function __convertStringToBool($sValue)
	{
		if(strcasecmp($sValue, 'true') == 0)
		{
			return true;
		}
		if(strcasecmp($sValue, 'false') == 0)
		{
			return false;
		}
		if(is_numeric($sValue))
		{
			if($sValue == 0)
			{
				return false;
			}
			return true;
		}
		return false;
	}
	
	private function __argumentStripSlashes(&$sArg)
	{
		if(!is_string($sArg))
		{
			return;
		}
		$sArg = stripslashes($sArg);
	}
	
	private function __convertValue($value)
	{
		$cType = substr($value, 0, 1);
		$sValue = substr($value, 1);
		switch ($cType)
		{
			case 'S':
				$value = ($sValue === false ? '' : $sValue);
				break;
			case 'B':
				$value = $this->__convertStringToBool($sValue);
				break;
			case 'N':
				$value = ($sValue == floor($sValue) ? (int)$sValue : (float)$sValue);
				break;
			case '*':
				$value = null;
				break;
		}
		return $value;
	}

	private function __argumentDecode( &$sArg )
	{
		if($sArg == '')
		{
			return;
		}

		$data = json_decode( $sArg , true );

		if($data !== null && $sArg != $data)
		{
			$sArg = $data;
		}
		else
		{
			$sArg = $this->__convertValue( $sArg );
		}
	}

	private function __argumentDecodeUTF8_iconv( &$mArg )
	{
		if( is_array( $mArg ) )
		{
			foreach($mArg as $sKey => $xArg)
			{
				$sNewKey = $sKey;
				$this->__argumentDecodeUTF8_iconv($sNewKey);
				
				if($sNewKey != $sKey)
				{
					$mArg[$sNewKey] = $xArg;
					unset($mArg[$sKey]);
					$sKey = $sNewKey;
				}
				
				$this->__argumentDecodeUTF8_iconv($xArg);
			}
		}
		else if(is_string($mArg))
		{
			$mArg = iconv("UTF-8", $this->getOption('characterEncoding') . '//TRANSLIT', $mArg);
		}
	}
	
	private function __argumentDecodeUTF8_mb_convert_encoding(&$mArg)
	{
		if(is_array($mArg))
		{
			foreach($mArg as $sKey => $xArg)
			{
				$sNewKey = $sKey;
				$this->__argumentDecodeUTF8_mb_convert_encoding($sNewKey);
				
				if($sNewKey != $sKey)
				{
					$mArg[$sNewKey] = $xArg;
					unset($mArg[$sKey]);
					$sKey = $sNewKey;
				}
				
				$this->__argumentDecodeUTF8_mb_convert_encoding($xArg);
			}
		}
		else if(is_string($mArg))
		{
			$mArg = mb_convert_encoding($mArg, $this->getOption('characterEncoding'), "UTF-8");
		}
	}
	
	private function __argumentDecodeUTF8_utf8_decode(&$mArg)
	{
		if(is_array($mArg))
		{
			foreach($mArg as $sKey => $xArg)
			{
				$sNewKey = $sKey;
				$this->__argumentDecodeUTF8_utf8_decode($sNewKey);
				
				if($sNewKey != $sKey)
				{
					$mArg[$sNewKey] = $xArg;
					unset($mArg[$sKey]);
					$sKey = $sNewKey;
				}
				
				$this->__argumentDecodeUTF8_utf8_decode($xArg);
			}
		}
		else if(is_string($mArg))
		{
			$mArg = utf8_decode($mArg);
		}
	}
	
	/*
		Constructor: __construct
		
		Initializes configuration settings to their default values and reads
		the argument data from the GET or POST data.
	*/
	private function __construct()
	{

		$this->aArgs = array();
		$this->nMethod = XAJAX_METHOD_UNKNOWN;
		
		if(isset($_POST['xjxargs']))
		{
			$this->nMethod = XAJAX_METHOD_POST;
			$this->aArgs = $_POST['xjxargs'];
		}
		else if(isset($_GET['xjxargs']))
		{
			$this->nMethod = XAJAX_METHOD_GET;
			$this->aArgs = $_GET['xjxargs'];
		}
		if(get_magic_quotes_gpc() == 1)
		{
			array_walk($this->aArgs, array(&$this, '__argumentStripSlashes'));
		}
		array_walk($this->aArgs, array(&$this, '__argumentDecode'));
	}
	
	/*
		Function: getInstance
		
		Returns:
		
		object - A reference to an instance of this class.  This function is
			used to implement the singleton pattern.
	*/
	public static function getInstance()
	{
		static $obj;
		if(!$obj)
		{
			$obj = new Manager();
		}
		return $obj;
	}
	
	/*
		Function: getRequestMethod
		
		Returns the method that was used to send the arguments from the client.
	*/
	public function getRequestMethod()
	{
		return $this->nMethod;
	}
	
	/*
		Function: process
		
		Returns the array of arguments that were extracted and parsed from 
		the GET or POST data.
	*/
 	public function process()
	{
		if(($this->getOption('decodeUTF8Input')))
		{
			$sFunction = '';
			
			if(function_exists('iconv'))
			{
				$sFunction = "iconv";
			}
			else if(function_exists('mb_convert_encoding'))
			{
				$sFunction = "mb_convert_encoding";
			}
			else if($this->getOption('characterEncoding') == "ISO-8859-1")
			{
				$sFunction = "utf8_decode";
			}
			else
			{
				throw new \Xajax\Exception\Error('errors.request.conversion');
			}

			$mFunction = array(&$this, '__argumentDecodeUTF8_' . $sFunction);
			array_walk($this->aArgs, $mFunction);
			$this->setOption('decodeUTF8Input', false);
		}
		
		return $this->aArgs;
	}
}
