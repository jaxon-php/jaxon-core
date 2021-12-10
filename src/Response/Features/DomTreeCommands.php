<?php

/**
 * DomTreeCommands.php - Provides DOM (HTML) related commands for the Response
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response\Features;

use Jaxon\Response\Response;

trait DomTreeCommands
{
    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param string        $sName              The command name
     * @param array         $aAttributes        Associative array of attributes that will describe the command
     * @param mixed         $mData              The data to be associated with this command
     * @param boolean       $bRemoveEmpty       If true, remove empty attributes
     *
     * @return Response
     */
    abstract protected function _addCommand($sName, array $aAttributes, $mData, $bRemoveEmpty = false);

    /**
     * Add a command to start a DOM response
     *
     * @return Response
     */
    public function domStartResponse()
    {
        return $this->_addCommand('DSR', [], '');
    }

    /**
     * Add a command to create a DOM element
     *
     * @param string        $variable            The DOM element name (id or class)
     * @param string        $tag                The HTML tag of the new DOM element
     *
     * @return Response
     */
    public function domCreateElement($variable, $tag)
    {
        $aAttributes = ['tgt' => $variable];
        return $this->_addCommand('DCE', $aAttributes, $tag);
    }

    /**
     * Add a command to set an attribute on a DOM element
     *
     * @param string        $variable            The DOM element name (id or class)
     * @param string        $key                The name of the attribute
     * @param string        $value                The value of the attribute
     *
     * @return Response
     */
    public function domSetAttribute($variable, $key, $value)
    {
        $aAttributes = [
            'tgt' => $variable,
            'key' => $key
        ];
        return $this->_addCommand('DSA', $aAttributes, $value);
    }

    /**
     * Add a command to remove children from a DOM element
     *
     * @param string        $parent             The DOM parent element
     * @param string        $skip               The number of children to skip
     * @param string        $remove             The number of children to remove
     *
     * @return Response
     */
    public function domRemoveChildren($parent, $skip = '', $remove = '')
    {
        $aAttributes = [
            'skip' => $skip,
            'remove' => $remove
        ];
        return $this->_addCommand('DRC', $aAttributes, $parent, true);
    }

    /**
     * Add a command to append a child to a DOM element
     *
     * @param string        $parent                The DOM parent element
     * @param string        $variable            The DOM element name (id or class)
     *
     * @return Response
     */
    public function domAppendChild($parent, $variable)
    {
        $aAttributes = ['par' => $parent];
        return $this->_addCommand('DAC', $aAttributes, $variable);
    }

    /**
     * Add a command to insert a DOM element before another
     *
     * @param string        $target                The DOM target element
     * @param string        $variable            The DOM element name (id or class)
     *
     * @return Response
     */
    public function domInsertBefore($target, $variable)
    {
        $aAttributes = ['tgt' => $target];
        return $this->_addCommand('DIB', $aAttributes, $variable);
    }

    /**
     * Add a command to insert a DOM element after another
     *
     * @param string        $target                The DOM target element
     * @param string        $variable            The DOM element name (id or class)
     *
     * @return Response
     */
    public function domInsertAfter($target, $variable)
    {
        $aAttributes = ['tgt' => $target];
        return $this->_addCommand('DIA', $aAttributes, $variable);
    }

    /**
     * Add a command to append a text to a DOM element
     *
     * @param string        $parent                The DOM parent element
     * @param string        $text                The HTML text to append
     *
     * @return Response
     */
    public function domAppendText($parent, $text)
    {
        $aAttributes = ['par' => $parent];
        return $this->_addCommand('DAT', $aAttributes, $text);
    }

    /**
     * Add a command to end a DOM response
     *
     * @return Response
     */
    public function domEndResponse()
    {
        return $this->_addCommand('DER', [], '');
    }
}
