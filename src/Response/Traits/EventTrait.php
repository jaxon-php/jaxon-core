<?php

/**
 * EventTrait.php
 *
 * Provides javascript related commands for the Response
 *
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Response\Traits;

use Jaxon\Response\ResponseInterface;
trait EventTrait
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
     * Add a command to install an event handler on the specified element
     *
     * You can add more than one event handler to an element's event using this method.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param string $sHandler    The name of the javascript function to call when the event is fired
     *
     * @return ResponseInterface
     */
    public function addHandler(string $sTarget, string $sEvent, string $sHandler): ResponseInterface
    {
        $aAttributes = ['id' => $sTarget, 'prop' => $sEvent];
        return $this->_addCommand('ah', $aAttributes, $sHandler);
    }

    /**
     * Add a command to remove an event handler from an element
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param string $sHandler    The name of the javascript function called when the event is fired
     *
     * @return ResponseInterface
     */
    public function removeHandler(string $sTarget, string $sEvent, string $sHandler): ResponseInterface
    {
        $aAttributes = ['id' => $sTarget, 'prop' => $sEvent];
        return $this->_addCommand('rh', $aAttributes, $sHandler);
    }

    /**
     * Add a command to add an event handler on the specified element
     * This handler can take custom parameters, and is is executed in a specific context.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param string $sHandler    The name of the javascript function to call when the event is fired
     * @param array $aParams    The parameters to pass to the event handler
     *
     * @return ResponseInterface
     */
    public function addEventHandler(string $sTarget, string $sEvent,
        string $sHandler, array $aParams = []): ResponseInterface
    {
        $aAttributes = ['id' => $sTarget, 'prop' => $sEvent, 'func' => $sHandler];
        return $this->_addCommand('ae', $aAttributes, $aParams);
    }

    /**
     * Add a command to set an event handler on the specified element
     * This handler can take custom parameters, and is is executed in a specific context.
     *
     * @param string $sTarget    The id of the element
     * @param string $sEvent    The name of the event
     * @param string $sHandler    The name of the javascript function to call when the event is fired
     * @param array $aParams    The parameters to pass to the event handler
     *
     * @return ResponseInterface
     */
    public function setEventHandler(string $sTarget, string $sEvent,
        string $sHandler, array $aParams = []): ResponseInterface
    {
        $aAttributes = ['id' => $sTarget, 'prop' => $sEvent, 'func' => $sHandler];
        return $this->_addCommand('se', $aAttributes, $aParams);
    }

    /**
     * Add a command to set a click handler on the browser
     *
     * @param string $sTarget    The id of the element that contains the event
     * @param string $sHandler    The name of the javascript function to call when the event is fired
     * @param array $aParams    The parameters to pass to the event handler
     *
     * @return ResponseInterface
     */
    public function onClick(string $sTarget, string $sHandler, array $aParams = []): ResponseInterface
    {
        return $this->setEventHandler($sTarget, 'onclick', $sHandler, $aParams);
    }
}
