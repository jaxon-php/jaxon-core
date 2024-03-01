<?php

/**
 * DomTrait.php
 *
 * Provides DOM (HTML) related commands for the Response
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response\Traits;

use Jaxon\Response\ResponseInterface;
use JsonSerializable;

trait HtmlDomTrait
{
     /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param string $sName    The command name
     * @param array|JsonSerializable $aOptions    The command options
     *
     * @return ResponseInterface
     */
    abstract public function addCommand(string $sName, array|JsonSerializable $aOptions): ResponseInterface;

    /**
     * Add a command to assign the specified value to the given element's attribute
     *
     * @param string $sTarget    The id of the html element on the browser
     * @param string $sAttribute    The attribute to be assigned
     * @param string $sValue    The value to be assigned to the attribute
     *
     * @return ResponseInterface
     */
    public function assign(string $sTarget, string $sAttribute, string $sValue): ResponseInterface
    {
        return $this->addCommand('as', [
            'id' => $this->str($sTarget),
            'attr' => $this->str($sAttribute),
            'value' => $this->str($sValue),
        ]);
    }

    /**
     * Add a command to assign the specified HTML content to the given element
     *
     * This is a shortcut for assign() on the innerHTML attribute.
     *
     * @param string $sTarget    The id of the html element on the browser
     * @param string $sValue    The value to be assigned to the attribute
     *
     * @return ResponseInterface
     */
    public function html(string $sTarget, string $sValue): ResponseInterface
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
     * @return ResponseInterface
     */
    public function append(string $sTarget, string $sAttribute, string $sValue): ResponseInterface
    {
        return $this->addCommand('ap', [
            'id' => $this->str($sTarget),
            'attr' => $this->str($sAttribute),
            'value' => $this->str($sValue),
        ]);
    }

    /**
     * Add a command to prepend the specified data to the given element's attribute
     *
     * @param string $sTarget    The id of the element to be updated
     * @param string $sAttribute    The name of the attribute to be prepended to
     * @param string $sValue    The value to be prepended to the attribute
     *
     * @return ResponseInterface
     */
    public function prepend(string $sTarget, string $sAttribute, string $sValue): ResponseInterface
    {
        return $this->addCommand('pp', [
            'id' => $this->str($sTarget),
            'attr' => $this->str($sAttribute),
            'value' => $this->str($sValue),
        ]);
    }

    /**
     * Add a command to replace a specified value with another value within the given element's attribute
     *
     * @param string $sTarget    The id of the element to update
     * @param string $sAttribute    The attribute to be updated
     * @param string $sSearch    The needle to search for
     * @param string $sReplace    The data to use in place of the needle
     *
     * @return ResponseInterface
     */
    public function replace(string $sTarget, string $sAttribute,
        string $sSearch, string $sReplace): ResponseInterface
    {
        return $this->addCommand('rp', [
            'id' => $this->str($sTarget),
            'attr' => $this->str($sAttribute),
            'search' => $this->str($sSearch),
            'replace' => $this->str($sReplace),
        ]);
    }

    /**
     * Add a command to clear the specified attribute of the given element
     *
     * @param string $sTarget    The id of the element to be updated.
     * @param string $sAttribute    The attribute to be cleared
     *
     * @return ResponseInterface
     */
    public function clear(string $sTarget, string $sAttribute = 'innerHTML'): ResponseInterface
    {
        return $this->assign($sTarget, $sAttribute, '');
    }

    /**
     * Add a command to remove an element from the document
     *
     * @param string $sTarget    The id of the element to be removed
     *
     * @return ResponseInterface
     */
    public function remove(string $sTarget): ResponseInterface
    {
        return $this->addCommand('rm', ['id' => $this->str($sTarget)]);
    }

    /**
     * Add a command to create a new element on the browser
     *
     * @param string $sParent    The id of the parent element
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return ResponseInterface
     */
    public function create(string $sParent, string $sTag, string $sId): ResponseInterface
    {
        return $this->addCommand('ce', [
            'id' => $this->str($sParent),
            'tag' => [
                'name' => $this->str($sTag),
                'id' => $this->str($sId),
            ],
        ]);
    }

    /**
     * Add a command to insert a new element just prior to the specified element
     *
     * @param string $sBefore    The id of the element used as a reference point for the insertion
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return ResponseInterface
     */
    public function insertBefore(string $sBefore, string $sTag, string $sId): ResponseInterface
    {
        return $this->addCommand('ie', [
            'id' => $this->str($sBefore),
            'tag' => [
                'name' => $this->str($sTag),
                'id' => $this->str($sId),
            ],
        ]);
    }

    /**
     * Add a command to insert a new element just prior to the specified element
     * This is an alias for insertBefore.
     *
     * @param string $sBefore    The id of the element used as a reference point for the insertion
     * @param string $sTag    The tag name to be used for the new element
     * @param string $sId    The id to assign to the new element
     *
     * @return ResponseInterface
     */
    public function insert(string $sBefore, string $sTag, string $sId): ResponseInterface
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
     * @return ResponseInterface
     */
    public function insertAfter(string $sAfter, string $sTag, string $sId): ResponseInterface
    {
        return $this->addCommand('ia', [
            'id' => $this->str($sAfter),
            'tag' => [
                'name' => $this->str($sTag),
                'id' => $this->str($sId),
            ],
        ]);
    }
}