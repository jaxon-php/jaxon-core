<?php

namespace Xajax\Request;

/*
 File: CustomRequest.php

 Contains the CustomRequest class

 Title: CustomRequest class

 Please see <copyright.php> for a detailed description, copyright and license information.
 */

/*
 @package Xajax
 @version $Id: CustomRequest.php 362 2007-05-29 15:32:24Z calltoconstruct $
 @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 @license http://www.xajaxproject.org/bsd_license.txt BSD License
 */

/*
	Class: CustomRequest
	
	This class extends the <xajaxRequest> class such that simple javascript
	can be put in place of a xajax request to the server.  The primary purpose
	of this class is to provide simple scripting services to the <xajaxControl>
	based objects, like <clsInput>, <clsTable> and <clsButton>.
*/
class CustomRequest extends Request
{
	/*
		Array: aVariables;
	*/
	var $aVariables;
	
	/*
		String: sScript;
	*/
	var $sScript;
	
	/*
		Function: __construct
		
		Constructs and initializes an instance of the object.
		
		Parameters:
		
		sScript - (string):  The javascript (template) that will be printed
			upon request.
		aVariables - (associative array, optional):  An array of variable name, 
			value pairs that will be passed to <xajaxCustomRequest->setVariable>
	*/
	public function __construct($sScript)
	{
		$this->aVariables = array();
		$this->sScript = $sScript;
	}
	
	/*
		Function: clearVariables
		
		Clears the array of variables that will be used to modify the script before
		it is printed and sent to the client.
	*/
	public function clearVariables()
	{
		$this->aVariables = array();
	}
	
	/*
		Function: setVariable
		
		Sets a value that will be used to modify the script before it is sent to
		the browser.  The <xajaxCustomRequest> object will perform a string 
		replace operation on each of the values set with this function.
		
		Parameters:
			$sName - (string): Variable name
			$sValue - (string): Value
		
	*/
	public function setVariable($sName, $sValue)
	{
		$this->aVariables[$sName] = $sValue;
	}
	
	/*
		Function: getScript
		
		Parameters:

		Returns a string representation of the script output (javascript) from 
		this request object.  See also:  <printScript>
	*/
	public function getScript()
	{
		$sScript = $this->sScript;
		foreach($this->aVariables as $sName => $sValue)
		{
			$sScript = str_replace($sName, $sValue, $sScript);
		}
		return $sScript;
	}
		
	/*
		Function: printScript
		
		Parameters:

		Generates a block of javascript code that can be used to invoke
		the specified xajax request.
	*/
	public function printScript()
	{
		echo $this->getScript();
	}
}
