<?php

/**
 * DomTreeTrait.php - Provides DOM (HTML) related commands for the Response
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response\Traits;

use Jaxon\Response\Response;

trait DomTreeTrait
{
    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param string $sName    The command name
     * @param array $aAttributes    Associative array of attributes that will describe the command
     * @param mixed $mData    The data to be associated with this command
     * @param bool $bRemoveEmpty    If true, remove empty attributes
     *
     * @return Response
     */
    abstract protected function _addCommand(string $sName, array $aAttributes, $mData, bool $bRemoveEmpty = false): Response;

    /**
     * Add a command to start a DOM response
     *
     * @return Response
     */
    public function domStartResponse(): Response
    {
        return $this->_addCommand('DSR', [], '');
    }

    /**
     * Add a command to create a DOM element
     *
     * @param string $sVariable    The DOM element name (id or class)
     * @param string $sTag    The HTML tag of the new DOM element
     *
     * @return Response
     */
    public function domCreateElement(string $sVariable, string $sTag): Response
    {
        $aAttributes = ['tgt' => $sVariable];
        return $this->_addCommand('DCE', $aAttributes, $sTag);
    }

    /**
     * Add a command to set an attribute on a DOM element
     *
     * @param string $sVariable    The DOM element name (id or class)
     * @param string $sKey    The name of the attribute
     * @param string $sValue    The value of the attribute
     *
     * @return Response
     */
    public function domSetAttribute(string $sVariable, string $sKey, string $sValue): Response
    {
        $aAttributes = ['tgt' => $sVariable, 'key' => $sKey];
        return $this->_addCommand('DSA', $aAttributes, $sValue);
    }

    /**
     * Add a command to remove children from a DOM element
     *
     * @param string $sParent    The DOM parent element
     * @param int $nSkip    The number of children to skip
     * @param int $nRemove    The number of children to remove
     *
     * @return Response
     */
    public function domRemoveChildren(string $sParent, int $nSkip = 0, int $nRemove = 0): Response
    {
        $aAttributes = ['skip' => $nSkip, 'remove' => $nRemove];
        return $this->_addCommand('DRC', $aAttributes, $sParent, true);
    }

    /**
     * Add a command to append a child to a DOM element
     *
     * @param string $sParent    The DOM parent element
     * @param string $sVariable    The DOM element name (id or class)
     *
     * @return Response
     */
    public function domAppendChild(string $sParent, string $sVariable): Response
    {
        $aAttributes = ['par' => $sParent];
        return $this->_addCommand('DAC', $aAttributes, $sVariable);
    }

    /**
     * Add a command to insert a DOM element before another
     *
     * @param string $sTarget    The DOM target element
     * @param string $sVariable    The DOM element name (id or class)
     *
     * @return Response
     */
    public function domInsertBefore(string $sTarget, string $sVariable): Response
    {
        $aAttributes = ['tgt' => $sTarget];
        return $this->_addCommand('DIB', $aAttributes, $sVariable);
    }

    /**
     * Add a command to insert a DOM element after another
     *
     * @param string $sTarget    The DOM target element
     * @param string $sVariable    The DOM element name (id or class)
     *
     * @return Response
     */
    public function domInsertAfter(string $sTarget, string $sVariable): Response
    {
        $aAttributes = ['tgt' => $sTarget];
        return $this->_addCommand('DIA', $aAttributes, $sVariable);
    }

    /**
     * Add a command to append a text to a DOM element
     *
     * @param string $sParent    The DOM parent element
     * @param string $sText    The HTML text to append
     *
     * @return Response
     */
    public function domAppendText(string $sParent, string $sText): Response
    {
        $aAttributes = ['par' => $sParent];
        return $this->_addCommand('DAT', $aAttributes, $sText);
    }

    /**
     * Add a command to end a DOM response
     *
     * @return Response
     */
    public function domEndResponse(): Response
    {
        return $this->_addCommand('DER', [], '');
    }
}
