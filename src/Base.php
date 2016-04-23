<?php

namespace Xajax;

/**
 * Xajax.php
 */
/*
 String: XAJAX_DEFAULT_CHAR_ENCODING

 Default character encoding used by both the <Xajax\Xajax> and
 <Xajax\Response\Response> classes.
 */
// if(!defined ('XAJAX_DEFAULT_CHAR_ENCODING')) define ('XAJAX_DEFAULT_CHAR_ENCODING', 'utf-8');

/*
 String: XAJAX_PROCESSING_EVENT
 String: XAJAX_PROCESSING_EVENT_BEFORE
 String: XAJAX_PROCESSING_EVENT_AFTER
 String: XAJAX_PROCESSING_EVENT_INVALID

 Identifiers used to register processing events.  Processing events are essentially
 hooks into the xajax core that can be used to add functionality into the request
 processing sequence.
*/
if(!defined ('XAJAX_PROCESSING_EVENT')) define ('XAJAX_PROCESSING_EVENT', 'xajax processing event');
if(!defined ('XAJAX_PROCESSING_EVENT_BEFORE')) define ('XAJAX_PROCESSING_EVENT_BEFORE', 'beforeProcessing');
if(!defined ('XAJAX_PROCESSING_EVENT_AFTER')) define ('XAJAX_PROCESSING_EVENT_AFTER', 'afterProcessing');
if(!defined ('XAJAX_PROCESSING_EVENT_INVALID')) define ('XAJAX_PROCESSING_EVENT_INVALID', 'invalidRequest');

/**
 * Plugin/Manager.php
 */
if(!defined('XAJAX_METHOD_UNKNOWN')) define('XAJAX_METHOD_UNKNOWN', 0);
if(!defined('XAJAX_METHOD_GET')) define('XAJAX_METHOD_GET', 1);
if(!defined('XAJAX_METHOD_POST')) define('XAJAX_METHOD_POST', 2);

/**
 * Request/Plugin/CallableObject.php
 */
/*
 Constant: XAJAX_CALLABLE_OBJECT
 Specifies that the item being registered via the <xajax->register> function is a
 object who's methods will be callable from the browser.
 */
if(!defined ('XAJAX_CALLABLE_OBJECT')) define ('XAJAX_CALLABLE_OBJECT', 'callable object');

/**
 * Request/Plugin/UserFunction.php
*/
/*
 Constant: XAJAX_FUNCTION
 Specifies that the item being registered via the <xajax->register> function
 is a php function available at global scope, or a specific function from
 an instance of an object.
*/
if(!defined ('XAJAX_FUNCTION')) define ('XAJAX_FUNCTION', 'user function');

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
if(!defined ('XAJAX_EVENT')) define ('XAJAX_EVENT', 'xajax event');
if(!defined ('XAJAX_EVENT_HANDLER')) define ('XAJAX_EVENT_HANDLER', 'xajax event handler');

/**
 * Request/Request.php
 */
/*
 Constant: XAJAX_FORM_VALUES
 Specifies that the parameter will consist of an array of form values.
 */
if(!defined ('XAJAX_FORM_VALUES')) define ('XAJAX_FORM_VALUES', 'get form values');
/*
 Constant: XAJAX_INPUT_VALUE
 Specifies that the parameter will contain the value of an input control.
*/
if(!defined ('XAJAX_INPUT_VALUE')) define ('XAJAX_INPUT_VALUE', 'get input value');
/*
 Constant: XAJAX_CHECKED_VALUE
 Specifies that the parameter will consist of a boolean value of a checkbox.
*/
if(!defined ('XAJAX_CHECKED_VALUE')) define ('XAJAX_CHECKED_VALUE', 'get checked value');
/*
 Constant: XAJAX_ELEMENT_INNERHTML
 Specifies that the parameter value will be the innerHTML value of the element.
*/
if(!defined ('XAJAX_ELEMENT_INNERHTML')) define ('XAJAX_ELEMENT_INNERHTML', 'get element innerHTML');
/*
 Constant: XAJAX_QUOTED_VALUE
 Specifies that the parameter will be a quoted value (string).
*/
if(!defined ('XAJAX_QUOTED_VALUE')) define ('XAJAX_QUOTED_VALUE', 'quoted value');
/*
 Constant: XAJAX_NUMERIC_VALUE
 Specifies that the parameter will be a numeric, non-quoted value.
*/
if(!defined ('XAJAX_NUMERIC_VALUE')) define ('XAJAX_NUMERIC_VALUE', 'numeric value');
/*
 Constant: XAJAX_JS_VALUE
 Specifies that the parameter will be a non-quoted value (evaluated by the
 browsers javascript engine at run time.
*/
if(!defined ('XAJAX_JS_VALUE')) define ('XAJAX_JS_VALUE', 'unquoted value');
/*
 Constant: XAJAX_PAGE_NUMBER
 Specifies that the parameter will be an integer used to generate pagination links.
*/
if(!defined ('XAJAX_PAGE_NUMBER')) define ('XAJAX_PAGE_NUMBER', 'page number');


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
