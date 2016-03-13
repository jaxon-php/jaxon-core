<?php

namespace Xajax\Response;

/*
	File: Control.php

	Contains the base class for all controls.

	Title: Control class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: Control.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Constant: XAJAX_HTML_CONTROL_DOCTYPE_FORMAT
	
	Defines the doctype of the current document; this will effect how the HTML is formatted
	when the html control library is used to construct html documents and fragments.  This can
	be one of the following values:
	
	'XHTML' - (default)  Typical effects are that certain elements are closed with '/>'
	'HTML' - Typical differences are that closing tags for certain elements cannot be '/>'
*/
if(false == defined('XAJAX_HTML_CONTROL_DOCTYPE_FORMAT')) define('XAJAX_HTML_CONTROL_DOCTYPE_FORMAT', 'XHTML');

/*
	Constant: XAJAX_HTML_CONTROL_DOCTYPE_VERSION
*/
if(false == defined('XAJAX_HTML_CONTROL_DOCTYPE_VERSION')) define('XAJAX_HTML_CONTROL_DOCTYPE_VERSION', '1.0');

/*
	Constant: XAJAX_HTML_CONTROL_DOCTYPE_VALIDATION
*/
if(false == defined('XAJAX_HTML_CONTROL_DOCTYPE_VALIDATION')) define('XAJAX_HTML_CONTROL_DOCTYPE_VALIDATION', 'TRANSITIONAL');


define('XAJAX_DOMRESPONSE_APPENDCHILD', 100);
define('XAJAX_DOMRESPONSE_INSERTBEFORE', 101);
define('XAJAX_DOMRESPONSE_INSERTAFTER', 102);
/*
	Class: Control

	The base class for all xajax enabled controls.  Derived classes will generate the
	HTML and javascript code that will be sent to the browser via <Control->printHTML>
	or sent to the browser in a <xajaxResponse> via <Control->getHTML>.
*/
class Control
{
	/*
		String: sTag
	*/
	protected $sTag;
	
	/*
		Boolean: sEndTag
		
		'required' - (default) Indicates the control must have a full end tag
		'optional' - The control may have an abbr. begin tag or a full end tag
		'forbidden' - The control must have an abbr. begin tag and no end tag
	*/
	protected $sEndTag;
	
	/*
		Array: aAttributes
		
		An associative array of attributes that will be used in the generation
		of the HMTL code for this control.
	*/
	protected $aAttributes;
	
	/*
		Array: aEvents
		
		An associative array of events that will be assigned to this control.  Each
		event declaration will include a reference to a <xajaxRequest> object; it's
		script will be extracted using <xajaxRequest->printScript> or 
		<xajaxRequest->getScript>.
	*/
	protected $aEvents;
	
	/*
		String: sClass
		
		Contains a declaration of the class of this control.  %inline controls do not 
		need to be indented, %block controls should be indented.
	*/
	protected $sClass;

	/*
		Function: Control
		
		Parameters:
		
		$aConfiguration - (array):  An associative array that contains a variety
			of configuration options for this <Control> object.
		
		Note:
		This array may contain the following entries:
		
		'attributes' - (array):  An associative array containing attributes
			that will be passed to the <Control->setAttribute> function.
		
		'children' - (array):  An array of <Control> derived objects that
			will be the children of this control.
	*/
	protected function __construct($sTag, $aConfiguration = array())
	{
		$this->sTag = $sTag;

		$this->clearAttributes();
				
		if(isset($aConfiguration['attributes']) && is_array($aConfiguration['attributes']))
        {
			foreach($aConfiguration['attributes'] as $sName => $sValue)
            {
				$this->setAttribute($sName, $sValue);
            }
        }

		$this->clearEvents();
		
		if(isset($aConfiguration['event']))
        {
			call_user_func_array(array($this, 'setEvent'), $aConfiguration['event']);
        }
		else if(isset($aConfiguration['events']) && is_array($aConfiguration['events']))
        {
			foreach($aConfiguration['events'] as $aEvent)
            {
				call_user_func_array(array($this, 'setEvent'), $aEvent);
            }
        }

		$this->sClass = '%block';
		$this->sEndTag = 'forbidden';
	}
	
	/*
		Function: getClass
		
		Returns the *adjusted* class of the element
	*/
	public function getClass()
	{
		return $this->sClass;
	}

	/*
		Function: clearAttributes
		
		Removes all attributes assigned to this control.
	*/
	public function clearAttributes()
	{
		$this->aAttributes = array();
	}

	/*
		Function: setAttribute
		
		Call to set various control specific attributes to be included in the HTML
		script that is returned when <Control->printHTML> or <Control->getHTML>
		is called.
		
		Parameters:
			$sName - (string): The attribute name to set the value.
			$sValue - (string): The value to be set.
	*/
	public function setAttribute($sName, $sValue)
	{
		$this->aAttributes[$sName] = $sValue;
	}
	
	/*
		Function: clearEvents
		
		Clear the events that have been associated with this object.
	*/
	public function clearEvents()
	{
		$this->aEvents = array();
	}

	/*
		Function: setEvent
		
		Call this function to assign a <xajaxRequest> object as the handler for
		the specific DOM event.  The <xajaxRequest->printScript> function will 
		be called to generate the javascript for this request.
		
		Parameters:
		
		sEvent - (string):  A string containing the name of the event to be assigned.
		objRequest - (xajaxRequest object):  The <xajaxRequest> object to be associated
			with the specified event.
		aParameters - (array, optional):  An array containing parameter declarations
			that will be passed to this <xajaxRequest> object just before the javascript
			is generated.
		sBeforeRequest - (string, optional):  a string containing a snippet of javascript code
			to execute prior to calling the xajaxRequest function
		sAfterRequest - (string, optional):  a string containing a snippet of javascript code
			to execute after calling the xajaxRequest function
	*/
	public function setEvent($sEvent, $objRequest, $aParameters = array(), $sBeforeRequest = '', $sAfterRequest = 'return false;')
	{
//SkipDebug
		if(!($objRequest instanceof \Xajax\Request\Request))
        {
			throw new \Xajax\Exception\Error('errors.response.result.invalid');
		}
//EndSkipDebug

		$objRequest = clone($objRequest);

		$this->aEvents[$sEvent] = array($objRequest, $aParameters, $sBeforeRequest, $sAfterRequest);
	}
	
	/*
		Function: getAttribute
		
		Call to obtain the value currently associated with the specified attribute
		if set.
		
		Parameters:
		
		sName - (string): The name of the attribute to be returned.
		
		Returns:
		
		mixed : The value associated with the attribute, or null.
	*/
	public function getAttribute($sName)
	{
		if(!isset($this->aAttributes[$sName]))
			return null;
		return $this->aAttributes[$sName];
	}

	protected function _getAttributes()
	{
		// NOTE: Special case here: disabled='false' does not work in HTML; does work in javascript
        $sAttributes = '';
		foreach($this->aAttributes as $sName => $sValue)
        {
			if($sName != 'disabled' || $sValue != 'false')
				$sAttributes .= "$sName='$sValue' ";
        }
        return $sAttributes;
	}

	protected function _getEvents()
	{
        $sEvents = '';
		foreach($this->aEvents as $sName => $aEvent)
		{
			$objRequest = $aEvent[0];
			$aParameters = $aEvent[1];
			$sBeforeRequest = $aEvent[2];
			$sAfterRequest = $aEvent[3];

			foreach($aParameters as $aParameter)
			{
				$nParameter = $aParameter[0];
				$sType = $aParameter[1];
				$sValue = $aParameter[2];
				$objRequest->setParameter($nParameter, $sType, $sValue);
			}

			$objRequest->useDoubleQuote();
			$sEvents .= "$sName='$sBeforeRequest" . $objRequest->getScript() . "$sAfterRequest' ";
		}
        return $sEvents;
	}

	/*
		Function: getHTML
		
		Generates and returns the HTML representation of this control and 
		it's children.
		
		Returns:
		
		string : The HTML representation of this control.
	*/
	public function getHTML($sIndent = '')
	{
		$sClass = $this->getClass();
		if($sClass == '%inline' || !($sIndent))
        {
			// Detect request for no formatting
			$sIndent = '';
        }

        $sStartTag = '<' . $this->sTag . ' ';
        $sEndTag = '';
		if($this->sEndTag == 'forbidden')
		{
			if('HTML' == XAJAX_HTML_CONTROL_DOCTYPE_FORMAT)
				$sEndTag = '>';
			else if('XHTML' == XAJAX_HTML_CONTROL_DOCTYPE_FORMAT)
				$sEndTag = '/>';
			if($sClass != '%inline' && ($sIndent))
				$sEndTag .= "\n";
		}
		else if($this->sEndTag == 'optional')
		{
			$sEndTag = '/>';
			if($sClass == '%inline' && ($sIndent))
				$sEndTag .= "\n";
		}

        return $sIndent . $sStartTag . $this->_getAttributes() . $this->_getEvents() . $sEndTag;
	}

	/*
		Function: printHTML
		
		Generates and prints the HTML representation of this control and 
		it's children.
		
		Returns:
		
		string : The HTML representation of this control.
	*/
	public function printHTML($sIndent = '')
	{
		print $this->getHTML($sIndent);
	}

	public function getResponse($count, $parent, $flag = XAJAX_DOMRESPONSE_APPENDCHILD)
	{
		$variable = "xjxElm[$count]";

		$response = $this->beginGetResponse($variable, $count);
		$this->getResponseAttributes($response, $variable);
		$this->getResponseEvents($response, $variable);
		$this->endGetResponse($response, $variable, $count, $parent, $flag);

		return $response;
	}

	protected function beginGetResponse($variable, $count)
	{
		$response = new xajaxResponse();

		if($count == 0)
			$response->domStartResponse();

		$response->domCreateElement($variable, $this->sTag);

		return $response;
	}

	protected function getResponseAttributes($response, $variable)
	{
		foreach($this->aAttributes as $sName => $sValue)
        {
			if($sName != 'disabled' || $sValue != 'false')
            {
				$response->domSetAttribute($variable, $sName, $sValue);
            }
        }
	}

	protected function endGetResponse($response, $variable, $count, $parent, $flag)
	{
        switch($flag)
        {
        case XAJAX_DOMRESPONSE_APPENDCHILD:
            $response->domAppendChild($parent, $variable);
            break;
        case XAJAX_DOMRESPONSE_INSERTBEFORE:
            $response->domInsertBefore($parent, $variable);
            break;
        case XAJAX_DOMRESPONSE_INSERTAFTER:
            $response->domInsertAfter($parent, $variable);
            break;
        default: break;
        }

		if($count == 0)
			$response->domEndResponse();
	}

	protected function getResponseEvents($response, $variable)
	{
		foreach($this->aEvents as $sName => $aEvent)
		{
			$objRequest = $aEvent[0];
			$aParameters = $aEvent[1];
			$sBeforeRequest = $aEvent[2];
			$sAfterRequest = $aEvent[3];

			foreach($aParameters as $aParameter)
			{
				$nParameter = $aParameter[0];
				$sType = $aParameter[1];
				$sValue = $aParameter[2];
				$objRequest->setParameter($nParameter, $sType, $sValue);
			}

			$objRequest->useDoubleQuote();

            $script = "$variable.$sName = function(evt) {if(!evt) var evt = window.event; $sBeforeRequest " .
                $objRequest->getScript() . "; $sAfterRequest }";
			$response->script($script);
		}
	}

	public function backtrace()
	{
		return '<div><div>Backtrace:</div><pre>' . print_r(debug_backtrace(), true) . '</pre></div>';
	}
}
