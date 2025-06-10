<?php

/**
 * NodeResponse.php
 *
 * This class is a special response class form Jaxon components.
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

use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Response\Manager\Command;
use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Script\Call\JxnCall;
use JsonSerializable;

class NodeResponse extends AjaxResponse
{
    /**
     * @var array
     */
    private $aComponent = [];

    /**
     * The constructor
     *
     * @param ResponseManager $xManager
     * @param PluginManager $xPluginManager
     * @param JxnCall $xJxnCall
     */
    public function __construct(ResponseManager $xManager,
        PluginManager $xPluginManager, JxnCall $xJxnCall)
    {
        parent::__construct($xManager, $xPluginManager);
        // The js class name is also the component name.
        $this->aComponent['name'] = $this->str($xJxnCall->_class());
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
     * @return Command
     */
    public function addCommand(string $sName, array|JsonSerializable $aArgs = [],
        bool $bRemoveEmpty = false): Command
    {
        return $this->xManager
            ->addCommand($sName, $aArgs, $bRemoveEmpty)
            ->setComponent($this->aComponent);
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
     * Add a command to assign the specified value to the given CSS attribute
     *
     * @param string $sCssAttribute    The CSS attribute to be assigned
     * @param string $sValue    The value to be assigned to the attribute
     *
     * @return self
     */
    public function style(string $sCssAttribute, string $sValue): self
    {
        return $this->assign("style.$sCssAttribute", $sValue);
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
    public function replace(string $sAttribute, string $sSearch, string $sReplace): self
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
