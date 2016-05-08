<?php

/**
 * Base.php - Xajax Base class
 *
 * This class defines base functionalities for the Xajax class, as welle as the library constants
 *
 * @package xajax-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax;


abstract class Base
{
	/*
	 * Processing events
	 */
	const PROCESSING_EVENT = 'xajax processing event';
	const PROCESSING_EVENT_BEFORE = 'beforeProcessing';
	const PROCESSING_EVENT_AFTER = 'afterProcessing';
	const PROCESSING_EVENT_INVALID = 'invalidRequest';

	/*
	 * Request methods
	 */
	const METHOD_UNKNOWN = 0;
	const METHOD_GET = 1;
	const METHOD_POST = 2;

	/*
	 * Request plugins
	 */
	// An object who's methods will be callable from the browser.
	const CALLABLE_OBJECT = 'callable object';
	// A php function available at global scope, or a specific function from an instance of an object.
	const USER_FUNCTION = 'user function';
	// A browser event.
	const BROWSER_EVENT = 'browser event';
	// An event handler.
	const EVENT_HANDLER = 'event handler';

	/*
	 * Request parameters
	 */
	// Specifies that the parameter will consist of an array of form values.
	const FORM_VALUES = 'get form values';
	// Specifies that the parameter will contain the value of an input control.
	const INPUT_VALUE = 'get input value';
	// Specifies that the parameter will consist of a boolean value of a checkbox.
	const CHECKED_VALUE = 'get checked value';
	// Specifies that the parameter value will be the innerHTML value of the element.
	const ELEMENT_INNERHTML = 'get element innerHTML';
	// Specifies that the parameter will be a quoted value (string).
	const QUOTED_VALUE = 'quoted value';
	// Specifies that the parameter will be a numeric, non-quoted value.
	const NUMERIC_VALUE = 'numeric value';
	// Specifies that the parameter will be a non-quoted value
	// (evaluated by the browsers javascript engine at run time).
	const JS_VALUE = 'unquoted value';
	// Specifies that the parameter will be an integer used to generate pagination links.
	const PAGE_NUMBER = 'page number';
}


/**
 * Xajax.php
 */

/*
 String: XAJAX_PROCESSING_EVENT
 String: XAJAX_PROCESSING_EVENT_BEFORE
 String: XAJAX_PROCESSING_EVENT_AFTER
 String: XAJAX_PROCESSING_EVENT_INVALID

 Identifiers used to register processing events.  Processing events are essentially
 hooks into the xajax core that can be used to add functionality into the request
 processing sequence.
 */
if(!defined ('XAJAX_PROCESSING_EVENT')) define ('XAJAX_PROCESSING_EVENT', Base::PROCESSING_EVENT);
if(!defined ('XAJAX_PROCESSING_EVENT_BEFORE')) define ('XAJAX_PROCESSING_EVENT_BEFORE', Base::PROCESSING_EVENT_BEFORE);
if(!defined ('XAJAX_PROCESSING_EVENT_AFTER')) define ('XAJAX_PROCESSING_EVENT_AFTER', Base::PROCESSING_EVENT_AFTER);
if(!defined ('XAJAX_PROCESSING_EVENT_INVALID')) define ('XAJAX_PROCESSING_EVENT_INVALID', Base::PROCESSING_EVENT_INVALID);

/**
 * Plugin/Manager.php
*/
if(!defined('XAJAX_METHOD_UNKNOWN')) define('XAJAX_METHOD_UNKNOWN', Base::METHOD_UNKNOWN);
if(!defined('XAJAX_METHOD_GET')) define('XAJAX_METHOD_GET', Base::METHOD_GET);
if(!defined('XAJAX_METHOD_POST')) define('XAJAX_METHOD_POST', Base::METHOD_POST);

/**
 * Request/Plugin/CallableObject.php
*/
/*
 Constant: XAJAX_CALLABLE_OBJECT
 Specifies that the item being registered via the <xajax->register> function is a
 object who's methods will be callable from the browser.
*/
if(!defined ('XAJAX_CALLABLE_OBJECT')) define ('XAJAX_CALLABLE_OBJECT', Base::CALLABLE_OBJECT);

/**
 * Request/Plugin/UserFunction.php
*/
/*
 Constant: XAJAX_FUNCTION
 Specifies that the item being registered via the <xajax->register> function
 is a php function available at global scope, or a specific function from
 an instance of an object.
*/
if(!defined ('XAJAX_FUNCTION')) define ('XAJAX_FUNCTION', Base::USER_FUNCTION);

/**
 * Request/Plugin/BrowserEvent.php
*/
/*
 Constant: XAJAX_EVENT
 Specifies that the item being registered via the <xajax->register> function
 is an event.

 Constant: XAJAX_EVENT_HANDLER
 Specifies that the item being registered via the <xajax->register> function
 is an event handler.
*/
if(!defined ('XAJAX_EVENT')) define ('XAJAX_EVENT', Base::BROWSER_EVENT);
if(!defined ('XAJAX_EVENT_HANDLER')) define ('XAJAX_EVENT_HANDLER', Base::EVENT_HANDLER);

/**
 * Request/Request.php
*/
/*
 Constant: XAJAX_FORM_VALUES
 Specifies that the parameter will consist of an array of form values.
*/
if(!defined ('XAJAX_FORM_VALUES')) define ('XAJAX_FORM_VALUES', Base::FORM_VALUES);
/*
 Constant: XAJAX_INPUT_VALUE
 Specifies that the parameter will contain the value of an input control.
*/
if(!defined ('XAJAX_INPUT_VALUE')) define ('XAJAX_INPUT_VALUE', Base::INPUT_VALUE);
/*
 Constant: XAJAX_CHECKED_VALUE
 Specifies that the parameter will consist of a boolean value of a checkbox.
*/
if(!defined ('XAJAX_CHECKED_VALUE')) define ('XAJAX_CHECKED_VALUE', Base::CHECKED_VALUE);
/*
 Constant: XAJAX_ELEMENT_INNERHTML
 Specifies that the parameter value will be the innerHTML value of the element.
*/
if(!defined ('XAJAX_ELEMENT_INNERHTML')) define ('XAJAX_ELEMENT_INNERHTML', Base::ELEMENT_INNERHTML);
/*
 Constant: XAJAX_QUOTED_VALUE
 Specifies that the parameter will be a quoted value (string).
*/
if(!defined ('XAJAX_QUOTED_VALUE')) define ('XAJAX_QUOTED_VALUE', Base::QUOTED_VALUE);
/*
 Constant: XAJAX_NUMERIC_VALUE
 Specifies that the parameter will be a numeric, non-quoted value.
*/
if(!defined ('XAJAX_NUMERIC_VALUE')) define ('XAJAX_NUMERIC_VALUE', Base::NUMERIC_VALUE);
/*
 Constant: XAJAX_JS_VALUE
 Specifies that the parameter will be a non-quoted value (evaluated by the
 browsers javascript engine at run time.
*/
if(!defined ('XAJAX_JS_VALUE')) define ('XAJAX_JS_VALUE', Base::JS_VALUE);
/*
 Constant: XAJAX_PAGE_NUMBER
 Specifies that the parameter will be an integer used to generate pagination links.
*/
if(!defined ('XAJAX_PAGE_NUMBER')) define ('XAJAX_PAGE_NUMBER', Base::PAGE_NUMBER);
