<?php

/**
 * DomTrait.php - Provides DOM (HTML) related commands for the Response
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response\Traits;

use Jaxon\Response\ResponseInterface;

trait DomTrait
{
    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param string $sName    The command name
     * @param array $aAttributes    Associative array of attributes that will describe the command
     * @param mixed $mData    The data to be associated with this command
     * @param bool $bRemoveEmpty    If true, remove empty attributes
     *
     * @return ResponseInterface
     */
    abstract protected function _addCommand(string $sName, array $aAttributes,
        $mData, bool $bRemoveEmpty = false): ResponseInterface;

    /**
     * Add a command to assign the specified value to the given element's attribute
     *
     * @param string $sTarget    The id of the html element on the browser
     * @param string $sAttribute    The attribute to be assigned
     * @param string $sData    The value to be assigned to the attribute
     *
     * @return ResponseInterface
     */
    public function assign(string $sTarget, string $sAttribute, string $sData): ResponseInterface
    {
        $aAttributes = ['id' => $sTarget, 'prop' => $sAttribute];
        return $this->_addCommand('as', $aAttributes, $sData);
    }

    /**
     * Add a command to assign the specified HTML content to the given element
     *
     * This is a shortcut for assign() on the innerHTML attribute.
     *
     * @param string $sTarget    The id of the html element on the browser
     * @param string $sData    The value to be assigned to the attribute
     *
     * @return ResponseInterface
     */
    public function html(string $sTarget, string $sData): ResponseInterface
    {
        return $this->assign($sTarget, 'innerHTML', $sData);
    }

    /**
     * Add a command to append the specified data to the given element's attribute
     *
     * @param string $sTarget    The id of the element to be updated
     * @param string $sAttribute    The name of the attribute to be appended to
     * @param string $sData    The data to be appended to the attribute
     *
     * @return ResponseInterface
     */
    public function append(string $sTarget, string $sAttribute, string $sData): ResponseInterface
    {
        $aAttributes = ['id' => $sTarget, 'prop' => $sAttribute];
        return $this->_addCommand('ap', $aAttributes, $sData);
    }

    /**
     * Add a command to prepend the specified data to the given element's attribute
     *
     * @param string $sTarget    The id of the element to be updated
     * @param string $sAttribute    The name of the attribute to be prepended to
     * @param string $sData    The value to be prepended to the attribute
     *
     * @return ResponseInterface
     */
    public function prepend(string $sTarget, string $sAttribute, string $sData): ResponseInterface
    {
        $aAttributes = ['id' => $sTarget, 'prop' => $sAttribute];
        return $this->_addCommand('pp', $aAttributes, $sData);
    }

    /**
     * Add a command to replace a specified value with another value within the given element's attribute
     *
     * @param string $sTarget    The id of the element to update
     * @param string $sAttribute    The attribute to be updated
     * @param string $sSearch    The needle to search for
     * @param string $sData    The data to use in place of the needle
     *
     * @return ResponseInterface
     */
    public function replace(string $sTarget, string $sAttribute, string $sSearch, string $sData): ResponseInterface
    {
        $aAttributes = ['id' => $sTarget, 'prop' => $sAttribute];
        $aData = ['s' => $sSearch, 'r' => $sData];
        return $this->_addCommand('rp', $aAttributes, $aData);
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
     * Add a command to assign a value to a member of a javascript object (or element)
     * that is specified by the context member of the request
     *
     * The object is referenced using the 'this' keyword in the sAttribute parameter.
     *
     * @param string $sAttribute    The attribute to be updated
     * @param string $sData    The value to assign
     *
     * @return ResponseInterface
     */
    public function contextAssign(string $sAttribute, string $sData): ResponseInterface
    {
        $aAttributes = ['prop' => $sAttribute];
        return $this->_addCommand('c:as', $aAttributes, $sData);
    }

    /**
     * Add a command to append a value onto the specified member of the javascript
     * context object (or element) specified by the context member of the request
     *
     * The object is referenced using the 'this' keyword in the sAttribute parameter.
     *
     * @param string $sAttribute    The attribute to be appended to
     * @param string $sData    The value to append
     *
     * @return ResponseInterface
     */
    public function contextAppend(string $sAttribute, string $sData): ResponseInterface
    {
        $aAttributes = ['prop' => $sAttribute];
        return $this->_addCommand('c:ap', $aAttributes, $sData);
    }

    /**
     * Add a command to prepend the speicified data to the given member of the current
     * javascript object specified by context in the current request
     *
     * The object is access via the 'this' keyword in the sAttribute parameter.
     *
     * @param string $sAttribute    The attribute to be updated
     * @param string $sData    The value to be prepended
     *
     * @return ResponseInterface
     */
    public function contextPrepend(string $sAttribute, string $sData): ResponseInterface
    {
        $aAttributes = ['prop' => $sAttribute];
        return $this->_addCommand('c:pp', $aAttributes, $sData);
    }

    /**
     * Add a command to to clear the value of the attribute specified in the sAttribute parameter
     *
     * The member is access via the 'this' keyword and can be used to update a javascript
     * object specified by context in the request parameters.
     *
     * @param string $sAttribute    The attribute to be cleared
     *
     * @return ResponseInterface
     */
    public function contextClear(string $sAttribute): ResponseInterface
    {
        return $this->contextAssign($sAttribute, '');
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
        $aAttributes = ['id' => $sTarget];
        return $this->_addCommand('rm', $aAttributes, '');
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
        $aAttributes = ['id' => $sParent, 'prop' => $sId];
        return $this->_addCommand('ce', $aAttributes, $sTag);
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
        $aAttributes = ['id' => $sBefore, 'prop' => $sId];
        return $this->_addCommand('ie', $aAttributes, $sTag);
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
        $aAttributes = ['id' => $sAfter, 'prop' => $sId];
        return $this->_addCommand('ia', $aAttributes, $sTag);
    }

    /**
     * Add a command to create an input element on the browser
     *
     * @param string $sParent    The id of the parent element
     * @param string $sType    The type of the new input element
     * @param string $sName    The name of the new input element
     * @param string $sId    The id of the new element
     *
     * @return ResponseInterface
     */
    public function createInput(string $sParent, string $sType, string $sName, string $sId): ResponseInterface
    {
        $aAttributes = ['id' => $sParent, 'prop' => $sId, 'type' => $sType];
        return $this->_addCommand('ci', $aAttributes, $sName);
    }

    /**
     * Add a command to insert a new input element preceding the specified element
     *
     * @param string $sBefore    The id of the element to be used as the reference point for the insertion
     * @param string $sType    The type of the new input element
     * @param string $sName    The name of the new input element
     * @param string $sId    The id of the new element
     *
     * @return ResponseInterface
     */
    public function insertInput(string $sBefore, string $sType, string $sName, string $sId): ResponseInterface
    {
        $aAttributes = ['id' => $sBefore, 'prop' => $sId, 'type' => $sType];
        return $this->_addCommand('ii', $aAttributes, $sName);
    }

    /**
     * Add a command to insert a new input element after the specified element
     *
     * @param string $sAfter    The id of the element to be used as the reference point for the insertion
     * @param string $sType    The type of the new input element
     * @param string $sName    The name of the new input element
     * @param string $sId    The id of the new element
     *
     * @return ResponseInterface
     */
    public function insertInputAfter(string $sAfter, string $sType, string $sName, string $sId): ResponseInterface
    {
        $aAttributes = ['id' => $sAfter, 'prop' => $sId, 'type' => $sType];
        return $this->_addCommand('iia', $aAttributes, $sName);
    }
}
