<?php

/**
 * jaxon.php
 *
 * The Jaxon class and namespaced global functions.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon;

use Jaxon\App\Ajax\Jaxon as JaxonLib;
use Jaxon\App\View\Helper\HtmlAttrHelper;
use Jaxon\Exception\SetupException;
use Jaxon\Script\Action\HtmlValue;
use Jaxon\Script\Action\PageValue;
use Jaxon\Script\Call\JqSelectorCall;
use Jaxon\Script\Call\JsObjectCall;
use Jaxon\Script\Call\JsSelectorCall;
use Jaxon\Script\Call\JxnCall;
use Jaxon\Script\ParameterFactory;
use Jaxon\Storage\StorageManager;

/**
 * Return the single instance of the Jaxon class
 *
 * @return JaxonLib
 */
function jaxon(): JaxonLib
{
    return JaxonLib::getInstance();
}

/**
 * Get an instance of a registered PHP class.
 *
 * @template T
 * @param class-string<T> $sClassName the class name
 *
 * @return T|null
 * @throws SetupException
 */
function cl(string $sClassName): mixed
{
    return jaxon()->cdi()->makeComponent($sClassName);
}

/**
 * Get a factory for a registered class.
 *
 * @param string $sClassName
 *
 * @return JxnCall
 */
function rq(string $sClassName = ''): JxnCall
{
    return jaxon()->di()->getCallFactory()->rq($sClassName);
}

/**
 * Get a factory for a Javascript object.
 *
 * @param string $sJsObject
 *
 * @return JsObjectCall
 */
function jo(string $sJsObject = ''): JsObjectCall
{
    return jaxon()->di()->getCallFactory()->jo($sJsObject);
}

/**
 * Get a factory for a JQuery selector.
 *
 * @param string $sPath    The jQuery selector path
 * @param mixed $xContext    A context associated to the selector
 *
 * @return JqSelectorCall
 */
function jq(string $sPath = '', $xContext = null): JqSelectorCall
{
    return jaxon()->di()->getCallFactory()->jq($sPath, $xContext);
}

/**
 * Get a factory for a Javascript element selector.
 *
 * @param string $sElementId
 *
 * @return JsSelectorCall
 */
function je(string $sElementId = ''): JsSelectorCall
{
    return jaxon()->di()->getCallFactory()->je($sElementId);
}

/**
 * Get the HTML attributes helper
 *
 * @return HtmlAttrHelper
 */
function attr(): HtmlAttrHelper
{
    return jaxon()->di()->getHtmlAttrHelper();
}

/**
 * Get the storage manager
 *
 * @return StorageManager
 */
function storage(): StorageManager
{
    return jaxon()->di()->g(StorageManager::class);
}

/**
 * Get the single instance of the parameter factory
 *
 * @return ParameterFactory
 * @deprecated Use the call factory functions instead.
 */
function pm(): ParameterFactory
{
    return jaxon()->di()->getParameterFactory();
}

/**
 * @param string sElementId
 *
 * @return array
 */
function form(string $sElementId): array
{
    return je($sElementId)->rd()->form();
}

/**
 * @param string sElementId
 *
 * @return array
 */
function checked(string $sElementId): array
{
    return je($sElementId)->rd()->checked();
}

/**
 * @param string sElementId
 *
 * @return HtmlValue
 */
function input(string $sElementId): HtmlValue
{
    return je($sElementId)->rd()->input();
}

/**
 * @param string sElementId
 *
 * @return HtmlValue
 */
function select(string $sElementId): HtmlValue
{
    return je($sElementId)->rd()->select();
}

/**
 * @param string sElementId
 *
 * @return HtmlValue
 */
function html(string $sElementId): HtmlValue
{
    return je($sElementId)->rd()->html();
}

/**
 * @return PageValue
 */
function page(): PageValue
{
    return je()->rd()->page();
}
