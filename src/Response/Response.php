<?php

/**
 * Response.php
 *
 * This class collects commands to be sent back to the browser in response to a jaxon request.
 * Commands are encoded and packaged in json format.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response;

use Jaxon\Script\JsExpr;

class Response extends AjaxResponse
{
    /**
     * @inheritDoc
     */
    protected function newResponse(): AjaxResponse
    {
        return $this->xManager->newResponse();
    }

    /**
     * Add a command to assign the specified value to the given element's attribute
     *
     * @param string $sTarget    The id of the html element on the browser
     * @param string $sAttribute    The attribute to be assigned
     * @param string $sValue    The value to be assigned to the attribute
     *
     * @return self
     */
    public function assign(string $sTarget, string $sAttribute, string $sValue): self
    {
        $this->addCommand('dom.assign', [
            'id' => $this->str($sTarget),
            'attr' => $this->str($sAttribute),
            'value' => $this->str($sValue),
        ]);
        return $this;
    }

    /**
     * Add a command to assign the specified HTML content to the given element
     *
     * This is a shortcut for assign() on the innerHTML attribute.
     *
     * @param string $sTarget    The id of the html element on the browser
     * @param string $sValue    The value to be assigned to the attribute
     *
     * @return self
     */
    public function html(string $sTarget, string $sValue): self
    {
        return $this->assign($sTarget, 'innerHTML', $sValue);
    }

    /**
     * Add a command to append the specified data to the given element's attribute
     *
     * @param string $sTarget    The id of the element to be updated
     * @param string $sAttribute    The name of the attribute to be appended to
     * @param string $sValue    The data to be appended to the attribute
     *
     * @return self
     */
    public function append(string $sTarget, string $sAttribute, string $sValue): self
    {
        $this->addCommand('dom.append', [
            'id' => $this->str($sTarget),
            'attr' => $this->str($sAttribute),
            'value' => $this->str($sValue),
        ]);
        return $this;
    }

    /**
     * Add a command to prepend the specified data to the given element's attribute
     *
     * @param string $sTarget    The id of the element to be updated
     * @param string $sAttribute    The name of the attribute to be prepended to
     * @param string $sValue    The value to be prepended to the attribute
     *
     * @return self
     */
    public function prepend(string $sTarget, string $sAttribute, string $sValue): self
    {
        $this->addCommand('dom.prepend', [
            'id' => $this->str($sTarget),
            'attr' => $this->str($sAttribute),
            'value' => $this->str($sValue),
        ]);
        return $this;
    }

    /**
     * Add a command to replace a specified value with another value within the given element's attribute
     *
     * @param string $sTarget    The id of the element to update
     * @param string $sAttribute    The attribute to be updated
     * @param string $sSearch    The needle to search for
     * @param string $sReplace    The data to use in place of the needle
     *
     * @return self
     */
    public function replace(string $sTarget, string $sAttribute,
        string $sSearch, string $sReplace): self
    {
        $this->addCommand('dom.replace', [
            'id' => $this->str($sTarget),
            'attr' => $this->str($sAttribute),
            'search' => $this->str($sSearch),
            'replace' => $this->str($sReplace),
        ]);
        return $this;
    }

    /**
     * Add a command to clear the specified attribute of the given element
     *
     * @param string $sTarget    The id of the element to be updated.
     * @param string $sAttribute    The attribute to be cleared
     *
     * @return self
     */
    public function clear(string $sTarget, string $sAttribute = 'innerHTML'): self
    {
        $this->addCommand('dom.clear', [
            'id' => $this->str($sTarget),
            'attr' => $this->str($sAttribute),
        ]);
        return $this;
    }

    /**
     * Add a command to remove an element from the document
     *
     * @param string $sTarget    The id of the element to be removed
     *
     * @return self
     */
    public function remove(string $sTarget): self
    {
        $this->addCommand('dom.remove', [
            'id' => $this->str($sTarget),
        ]);
        return $this;
    }

    /**
     * Add a command to create a new element on the browser
     *
     * @param string $sParent    The id of the parent element
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return self
     */
    public function create(string $sParent, string $sTag, string $sId): self
    {
        $this->addCommand('dom.create', [
            'id' => $this->str($sParent),
            'tag' => [
                'name' => $this->str($sTag),
                'id' => $this->str($sId),
            ],
        ]);
        return $this;
    }

    /**
     * Add a command to insert a new element just prior to the specified element
     *
     * @param string $sBefore    The id of the element used as a reference point for the insertion
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return self
     */
    public function insertBefore(string $sBefore, string $sTag, string $sId): self
    {
        $this->addCommand('dom.insert.before', [
            'id' => $this->str($sBefore),
            'tag' => [
                'name' => $this->str($sTag),
                'id' => $this->str($sId),
            ],
        ]);
        return $this;
    }

    /**
     * Add a command to insert a new element just prior to the specified element
     * This is an alias for insertBefore.
     *
     * @param string $sBefore    The id of the element used as a reference point for the insertion
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return self
     */
    public function insert(string $sBefore, string $sTag, string $sId): self
    {
        return $this->insertBefore($sBefore, $sTag, $sId);
    }

    /**
     * Add a command to insert a new element after the specified
     *
     * @param string $sAfter    The id of the element used as a reference point for the insertion
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return self
     */
    public function insertAfter(string $sAfter, string $sTag, string $sId): self
    {
        $this->addCommand('dom.insert.after', [
            'id' => $this->str($sAfter),
            'tag' => [
                'name' => $this->str($sTag),
                'id' => $this->str($sId),
            ],
        ]);
        return $this;
    }

    /**
     * Add a command to set an event handler on the specified element
     * This handler can take custom parameters, and is is executed in a specific context.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param JsExpr $xCall    The event handler
     *
     * @return self
     */
    public function setEventHandler(string $sTarget, string $sEvent, JsExpr $xCall): self
    {
        $this->addCommand('handler.event.set', [
            'id' => $this->str($sTarget),
            'event' => $this->str($sEvent),
            'func' => $xCall,
        ]);
        return $this;
    }

    /**
     * Add a command to set a click handler on the browser
     *
     * @param string $sTarget    The id of the element
     * @param JsExpr $xCall    The event handler
     *
     * @return self
     */
    public function onClick(string $sTarget, JsExpr $xCall): self
    {
        return $this->setEventHandler($sTarget, 'onclick', $xCall);
    }

    /**
     * Add a command to add an event handler on the specified element
     * This handler can take custom parameters, and is is executed in a specific context.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param JsExpr $xCall    The event handler
     *
     * @return self
     */
    public function addEventHandler(string $sTarget, string $sEvent, JsExpr $xCall): self
    {
        $this->addCommand('handler.event.add', [
            'id' => $this->str($sTarget),
            'event' => $this->str($sEvent),
            'func' => $xCall,
        ]);
        return $this;
    }

    /**
     * Add a command to install an event handler on the specified element
     *
     * You can add more than one event handler to an element's event using this method.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param string $sHandler    The name of the javascript function to call when the event is fired
     *
     * @return self
     */
    public function addHandler(string $sTarget, string $sEvent, string $sHandler): self
    {
        $this->addCommand('handler.add', [
            'id' => $this->str($sTarget),
            'event' => $this->str($sEvent),
            'func' => $this->str($sHandler),
        ]);
        return $this;
    }

    /**
     * Add a command to remove an event handler from an element
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param string $sHandler    The name of the javascript function called when the event is fired
     *
     * @return self
     */
    public function removeHandler(string $sTarget, string $sEvent, string $sHandler): self
    {
        $this->addCommand('handler.remove', [
            'id' => $this->str($sTarget),
            'event' => $this->str($sEvent),
            'func' => $this->str($sHandler),
        ]);
        return $this;
    }
}
