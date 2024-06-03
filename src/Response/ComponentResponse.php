<?php

/**
 * ComponentResponse.php
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

use Jaxon\App\Dialog\DialogManager;
use Jaxon\JsCall\JsExpr;
use Jaxon\Plugin\Manager\PluginManager;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface as PsrRequestInterface;

class ComponentResponse extends AjaxResponse
{
    /**
     * @var array
     */
    protected $aComponent = [];

    /**
     * The constructor
     *
     * @param ResponseManager $xManager
     * @param Psr17Factory $xPsr17Factory
     * @param PsrRequestInterface $xRequest
     * @param PluginManager $xPluginManager
     * @param DialogManager $xDialogManager
     * @param string $sComponentName
     */
    public function __construct(ResponseManager $xManager, Psr17Factory $xPsr17Factory,
        PsrRequestInterface $xRequest, PluginManager $xPluginManager,
        DialogManager $xDialogManager, string $sComponentName)
    {
        parent::__construct($xManager, $xPsr17Factory, $xRequest, $xPluginManager, $xDialogManager);
        $this->aComponent['name'] = $this->str($sComponentName);
    }

    /**
     * @inheritDoc
     */
    protected function newResponse(): AjaxResponse
    {
        return $this->xManager->newComponentResponse(self::class);
    }

    /**
     * Set the component item
     *
     * @param string $sItem
     *
     * @return self
     */
    public function item(string $sItem = 'main'): self
    {
        $this->aComponent['item'] = $this->str($sItem);
        return $this;
    }

    /**
     * Add a command to assign the specified value to the given element's attribute
     *
     * @param string $sAttribute    The attribute to be assigned
     * @param string $sValue    The value to be assigned to the attribute
     *
     * @return self
     */
    public function assign(string $sAttribute, string $sValue): self
    {
        $this->addCommand('dom.assign', [
            'component' => $this->aComponent,
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
     * @param string $sValue    The value to be assigned to the attribute
     *
     * @return self
     */
    public function html(string $sValue): self
    {
        return $this->assign('innerHTML', $sValue);
    }

    /**
     * Add a command to append the specified data to the given element's attribute
     *
     * @param string $sAttribute    The name of the attribute to be appended to
     * @param string $sValue    The data to be appended to the attribute
     *
     * @return self
     */
    public function append(string $sAttribute, string $sValue): self
    {
        $this->addCommand('dom.append', [
            'component' => $this->aComponent,
            'attr' => $this->str($sAttribute),
            'value' => $this->str($sValue),
        ]);
        return $this;
    }

    /**
     * Add a command to prepend the specified data to the given element's attribute
     *
     * @param string $sAttribute    The name of the attribute to be prepended to
     * @param string $sValue    The value to be prepended to the attribute
     *
     * @return self
     */
    public function prepend(string $sAttribute, string $sValue): self
    {
        $this->addCommand('dom.prepend', [
            'component' => $this->aComponent,
            'attr' => $this->str($sAttribute),
            'value' => $this->str($sValue),
        ]);
        return $this;
    }

    /**
     * Add a command to replace a specified value with another value within the given element's attribute
     *
     * @param string $sAttribute    The attribute to be updated
     * @param string $sSearch    The needle to search for
     * @param string $sReplace    The data to use in place of the needle
     *
     * @return self
     */
    public function replace(string $sAttribute,
        string $sSearch, string $sReplace): self
    {
        $this->addCommand('dom.replace', [
            'component' => $this->aComponent,
            'attr' => $this->str($sAttribute),
            'search' => $this->str($sSearch),
            'replace' => $this->str($sReplace),
        ]);
        return $this;
    }

    /**
     * Add a command to clear the specified attribute of the given element
     *
     * @param string $sAttribute    The attribute to be cleared
     *
     * @return self
     */
    public function clear(string $sAttribute = 'innerHTML'): self
    {
        $this->addCommand('dom.clear', [
            'component' => $this->aComponent,
            'attr' => $this->str($sAttribute),
        ]);
        return $this;
    }

    /**
     * Add a command to remove an element from the document
     *
     * @return self
     */
    public function remove(): self
    {
        $this->addCommand('dom.remove', [
            'component' => $this->aComponent,
        ]);
        return $this;
    }

    /**
     * Add a command to create a new element on the browser
     *
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return self
     */
    public function create(string $sTag, string $sId): self
    {
        $this->addCommand('dom.create', [
            'component' => $this->aComponent,
            'tag' => [
                'name' => $this->str($sTag),
                'component' => $this->aComponent,
            ],
        ]);
        return $this;
    }

    /**
     * Add a command to insert a new element just prior to the specified element
     *
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return self
     */
    public function insertBefore(string $sTag, string $sId): self
    {
        $this->addCommand('dom.insert.before', [
            'component' => $this->aComponent,
            'tag' => [
                'name' => $this->str($sTag),
                'component' => $this->aComponent,
            ],
        ]);
        return $this;
    }

    /**
     * Add a command to insert a new element just prior to the specified element
     * This is an alias for insertBefore.
     *
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return self
     */
    public function insert(string $sTag, string $sId): self
    {
        return $this->insertBefore($sTag, $sId);
    }

    /**
     * Add a command to insert a new element after the specified
     *
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return self
     */
    public function insertAfter(string $sTag, string $sId): self
    {
        $this->addCommand('dom.insert.after', [
            'component' => $this->aComponent,
            'tag' => [
                'name' => $this->str($sTag),
                'component' => $this->aComponent,
            ],
        ]);
        return $this;
    }

    /**
     * Add a command to set an event handler on the specified element
     * This handler can take custom parameters, and is is executed in a specific context.
     *
     * @param string $sEvent    The name of the event
     * @param JsExpr $xCall    The event handler
     *
     * @return self
     */
    public function setEventHandler(string $sEvent, JsExpr $xCall): self
    {
        $this->addCommand('handler.event.set', [
            'component' => $this->aComponent,
            'event' => $this->str($sEvent),
            'func' => $xCall,
        ]);
        return $this;
    }

    /**
     * Add a command to set a click handler on the browser
     *
     * @param JsExpr $xCall    The event handler
     *
     * @return self
     */
    public function onClick(JsExpr $xCall): self
    {
        return $this->setEventHandler('onclick', $xCall);
    }

    /**
     * Add a command to add an event handler on the specified element
     * This handler can take custom parameters, and is is executed in a specific context.
     *
     * @param string $sEvent    The name of the event
     * @param JsExpr $xCall    The event handler
     *
     * @return self
     */
    public function addEventHandler(string $sEvent, JsExpr $xCall): self
    {
        $this->addCommand('handler.event.add', [
            'component' => $this->aComponent,
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
     * @param string $sEvent    The name of the event
     * @param string $sHandler    The name of the javascript function to call when the event is fired
     *
     * @return self
     */
    public function addHandler(string $sEvent, string $sHandler): self
    {
        $this->addCommand('handler.add', [
            'component' => $this->aComponent,
            'event' => $this->str($sEvent),
            'func' => $this->str($sHandler),
        ]);
        return $this;
    }

    /**
     * Add a command to remove an event handler from an element
     *
     * @param string $sEvent    The name of the event
     * @param string $sHandler    The name of the javascript function called when the event is fired
     *
     * @return self
     */
    public function removeHandler(string $sEvent, string $sHandler): self
    {
        $this->addCommand('handler.remove', [
            'component' => $this->aComponent,
            'event' => $this->str($sEvent),
            'func' => $this->str($sHandler),
        ]);
        return $this;
    }
}
