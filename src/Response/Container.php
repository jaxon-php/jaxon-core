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
	Class: Container
	
	This class is used as the base class for controls that will contain
	other child controls.
*/
class Container extends Control
{
	/*
		Array: aChildren
		
		An array of child controls.
	*/
	protected $aChildren;

	/*
		Boolean: sChildClass
		
		Will contain '%inline' if all children are class = '%inline', '%block' if all children are '%block' or
		'%flow' if both '%inline' and '%block' elements are detected.
	*/
	protected $sChildClass;

	/*
		Function: __construct
		
		Called to construct and configure this control.
		
		Parameters:
		
		aConfiguration - (array):  See <Control->Control> for more
			information.
	*/
	protected function __construct($sTag, $aConfiguration=array())
	{
		parent::__construct($sTag, $aConfiguration);

		$this->clearChildren();
		
		if(isset($aConfiguration['child']))
		{
			$this->addChild($aConfiguration['child']);
		}
		else if(isset($aConfiguration['children']))
		{
			$this->addChildren($aConfiguration['children']);
		}

		$this->sEndTag = 'required';
	}
	
	/*
		Function: getClass
		
		Returns the *adjusted* class of the element
	*/
	public function getClass()
	{
		$sClass = Control::getClass();
		
		if(count($this->aChildren) > 0 && $sClass == '%flow')
		{
			return $this->getContentClass();
		}
		else if(count($this->aChildren) == 0 || $sClass == '%inline' || $sClass == '%block')
		{
			return $sClass;
		}
		// The class is invalid
		throw new \Xajax\Exception\Error('errors.response.class.invalid', array('name' => $sClass));
	}
	
	/*
		Function: getContentClass
		
		Returns the *adjusted* class of the content (children) of this element
	*/
	public function getContentClass()
	{
		$sClass = '';
		
		foreach($this->aChildren as $xChild)
		{
			if($sClass == '')
			{
				$sClass = $xChild->getClass();
			}
			else if($xChild->getClass() != $sClass)
			{
				$sClass = '%flow';
			}
		}
		if($sClass == '')
		{
			$sClass = '%inline';
		}	
		return $sClass;
	}
	
	/*
		Function: clearChildren
		
		Clears the list of child controls associated with this control.
	*/
	public function clearChildren()
	{
		$this->sChildClass = '%inline';
		$this->aChildren = array();
	}

	/*
		Function: addChild
		
		Adds a control to the array of child controls.  Child controls
		must be derived from <Control>.
	*/
	public function addChild($objControl)
	{
//SkipDebug
		if(!($objControl instanceof Control))
		{
			throw new \Xajax\Exception\Error('errors.response.control.invalid', array('class' => get_class($objControl)));
		}
//EndSkipDebug

		$this->aChildren[] = $objControl;
	}
	
	public function addChildren($aChildren)
	{
//SkipDebug
		if(!is_array($aChildren))
		{
			throw new \Xajax\Exception\Error('errors.response.parameter.invalid');
		}
//EndSkipDebug
				
		foreach($aChildren as $xChild)
		{
			$this->addChild($xChild);
		}
	}

	protected function _getChildren($sIndent = '')
	{
		if(!($this instanceof clsDocument ) && ($sIndent))
		{
			$sIndent .= "\t";
		}

		// children
		$html = $sIndent;
		foreach($this->aChildren as $xChild)
		{
			$html .= $xChild->getHTML($sIndent);
		}
		return $html;
	}

	public function getHTML($sIndent = '')
	{
		$sClass = $this->getClass();
		$sContentClass = $this->getContentClass();

		if($sClass == '%inline' || !($sIndent))
		{
			$sIndent = '';
		}
			
        $sStartTag = '<' . $this->sTag . ' ';
        $sEndTag = '';
		$sCloseTag = '';
		
		if(count($this->aChildren) == 0)
		{
			// If there is no child, the HTML element has no content
			if($this->sEndTag == 'optional')
			{
				$sEndTag .= '/>';
				if($sClass == '%inline' && ($sIndent))
				{
					$sEndTag .= "\n";
				}
			}
		}
		else
		{
			$sEndTag = '>';
			if($sContentClass != '%inline' && ($sIndent))
			{
				$sEndTag .= "\n";
			}
		}
		
		if($sContentClass != '%inline' && ($sIndent))
		{
			$sCloseTag .= $sIndent;
		}
		
		$sCloseTag = '</' . $this->sTag . '>';
		
		if($sClass != '%inline' && ($sIndent))
		{
			$sCloseTag .= "\n";
		}

		return $sIndent . $sStartTag . $this->_getAttributes() . $this->_getEvents() . $sEndTag
			. $this->_getChildren($sIndent) . $sCloseTag;
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
		$this->getResponseChildren($response, $variable, $count);
		$this->endGetResponse($response, $variable, $count, $parent, $flag);

		return $response;
	}

	protected function getResponseChildren($response, $variable, $count)
	{
		foreach($this->aChildren as $xChild)
		{
			$response->appendResponse($xChild->getResponse($count + 1, $variable));
		}
	}
}
