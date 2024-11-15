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
use Jaxon\Plugin\Manager\PluginManager;
use JsonSerializable;

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
     * @param PluginManager $xPluginManager
     * @param DialogManager $xDialogManager
     * @param string $sComponentName
     */
    public function __construct(ResponseManager $xManager, PluginManager $xPluginManager,
        DialogManager $xDialogManager, string $sComponentName)
    {
        parent::__construct($xManager, $xPluginManager, $xDialogManager);
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
     * Add a response command to the array of commands
     *
     * @param string $sName    The command name
     * @param array|JsonSerializable $aArgs    The command arguments
     * @param bool $bRemoveEmpty
     *
     * @return void
     */
    public function addCommand(string $sName, array|JsonSerializable $aArgs = [],
        bool $bRemoveEmpty = false)
    {
        parent::addCommand($sName, $aArgs, $bRemoveEmpty);
        $this->xManager->setComponent($this->aComponent);
    }

    /**
     * Insert a response command before a given number of commands
     *
     * @param string $sName    The command name
     * @param array|JsonSerializable $aArgs    The command arguments
     * @param int $nBefore    The number of commands to move
     * @param bool $bRemoveEmpty
     *
     * @return void
     */
    public function insertCommand(string $sName, array|JsonSerializable $aArgs,
        int $nBefore, bool $bRemoveEmpty = false)
    {
        parent::insertCommand($sName, $aArgs, $nBefore, $bRemoveEmpty);
        $this->xManager->setComponent($this->aComponent);
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
        $this->addCommand('node.assign', [
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
        $this->addCommand('node.append', [
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
        $this->addCommand('node.prepend', [
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
        $this->addCommand('node.replace', [
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
        $this->addCommand('node.clear', [
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
        $this->addCommand('node.remove', []);
        return $this;
    }
}
