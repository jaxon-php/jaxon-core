<?php

/**
 * ResponseManager.php - Jaxon Response Manager
 *
 * This class stores and tracks the response that will be returned after processing a request.
 * The Response Manager represents a single point of contact for working with <ResponsePlugin> objects.
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

use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use JsonSerializable;

use function array_filter;
use function count;
use function trim;

class ResponseManager
{
    /**
     * @var Container
     */
    private $di;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * @var string
     */
    private $sCharacterEncoding;

    /**
     * The current response object that will be sent back to the browser
     * once the request processing phase is complete
     *
     * @var AbstractResponse
     */
    private $xResponse = null;

    /**
     * The error message
     *
     * @var string
     */
    private $sErrorMessage = '';

    /**
     * The debug messages
     *
     * @var array
     */
    private $aDebugMessages = [];

    /**
     * The commands that will be sent to the browser in the response
     *
     * @var array
     */
    protected $aCommands = [];

    /**
     * The latest added command position
     *
     * @var int
     */
    private $nLastCommandPos = -1;

    /**
     * @param Container $di
     * @param Translator $xTranslator
     * @param string $sCharacterEncoding
     */
    public function __construct(Container $di, Translator $xTranslator, string $sCharacterEncoding)
    {
        $this->di = $di;
        $this->xTranslator = $xTranslator;
        $this->sCharacterEncoding = $sCharacterEncoding;
    }

    /**
     * Convert to string
     *
     * @param mixed $xData
     *
     * @return string
     */
    protected function str($xData): string
    {
        return trim((string)$xData, " \t\n");
    }

    /**
     * Set the error message
     *
     * @param string $sErrorMessage
     *
     * @return void
     */
    public function setErrorMessage(string $sErrorMessage)
    {
        $this->sErrorMessage = $sErrorMessage;
    }

    /**
     * Get the error message
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->sErrorMessage;
    }

    /**
     * Get the commands in the response
     *
     * @return array
     */
    public function getCommands(): array
    {
        return $this->aCommands;
    }

    /**
     * Get the number of commands in the response
     *
     * @return int
     */
    public function getCommandCount(): int
    {
        return count($this->aCommands);
    }

    /**
     * Clear all the commands already added to the response
     *
     * @return void
     */
    public function clearCommands()
    {
        $this->aCommands = [];
    }

    /**
     * @param array|JsonSerializable $aArgs    The command arguments
     * @param bool $bRemoveEmpty    If true, remove empty arguments
     *
     * @return array
     */
    private function getCommandArgs(array|JsonSerializable $aArgs, bool $bRemoveEmpty = false): array
    {
        if(!$bRemoveEmpty)
        {
            return $aArgs;
        }
        return array_filter($aArgs, function($xArg) {
            return empty($xArg);
        });
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
    public function addCommand(string $sName, array|JsonSerializable $aArgs,
        bool $bRemoveEmpty = false)
    {
        $this->nLastCommandPos = count($this->aCommands);
        $this->aCommands[] = [
            'name' => $this->str($sName),
            'args' => $this->getCommandArgs($aArgs, $bRemoveEmpty),
        ];
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
        $nInsertPosition = count($this->aCommands) - $nBefore;
        if($nInsertPosition < 0)
        {
            return;
        }
        // Move the commands after the insert position.
        for($nPos = count($this->aCommands); $nPos > $nInsertPosition; $nPos--)
        {
            $this->aCommands[$nPos] = $this->aCommands[$nPos - 1];
        }
        $this->nLastCommandPos = $nInsertPosition;
        $this->aCommands[$nInsertPosition] = [
            'name' => $this->str($sName),
            'args' => $this->getCommandArgs($aArgs, $bRemoveEmpty),
        ];
    }

    /**
     * Set a component on the last command
     *
     * @param array $aComponent
     *
     * @return void
     */
    public function setComponent(array $aComponent)
    {
        if($this->nLastCommandPos >= 0)
        {
            $this->aCommands[$this->nLastCommandPos]['component'] = $aComponent;
        }
    }

    /**
     * Add an option on the last command
     *
     * @param string $sName    The option name
     * @param string|array|JsonSerializable $xValue    The option value
     *
     * @return void
     */
    public function setOption(string $sName, string|array|JsonSerializable $xValue)
    {
        if($this->nLastCommandPos >= 0)
        {
            $aCommand = &$this->aCommands[$this->nLastCommandPos];
            if(isset($aCommand['options']))
            {
                $aCommand['options'][$this->str($sName)] = $xValue;
                return;
            }
            $aCommand['options'] = [$this->str($sName) => $xValue];
        }
    }

    /**
     * Get the response to the Jaxon request
     *
     * @param AbstractResponse $xResponse
     *
     * @return void
     */
    public function setResponse(AbstractResponse $xResponse)
    {
        $this->xResponse = $xResponse;
    }

    /**
     * Get the response to the Jaxon request
     *
     * @return AbstractResponse
     */
    public function getResponse()
    {
        if(!$this->xResponse)
        {
            $this->xResponse = $this->di->getResponse();
        }
        return $this->xResponse;
    }

    /**
     * Create a new Jaxon response
     *
     * @return Response
     */
    public function newResponse(): Response
    {
        return $this->di->newResponse();
    }

    /**
     * Create a new reponse for a Jaxon component
     *
     * @param string $sComponentClass
     *
     * @return ComponentResponse
     */
    public function newComponentResponse(string $sComponentClass): ComponentResponse
    {
        return $this->di->newComponentResponse($sComponentClass);
    }

    /**
     * Appends a debug message on the end of the debug message queue
     *
     * Debug messages will be sent to the client with the normal response
     * (if the response object supports the sending of debug messages, see: <ResponsePlugin>)
     *
     * @param string $sMessage    The debug message
     *
     * @return void
     */
    public function debug(string $sMessage)
    {
        $this->aDebugMessages[] = $sMessage;
    }

    /**
     * Clear the response and appends a debug message on the end of the debug message queue
     *
     * @param string $sMessage The debug message
     *
     * @return void
     */
    public function error(string $sMessage)
    {
        $this->clearCommands();
        $this->debug($sMessage);
    }

    /**
     * Prints the debug messages into the current response object
     *
     * @return void
     */
    public function printDebug()
    {
        foreach($this->aDebugMessages as $sMessage)
        {
            $this->addCommand('script.debug', ['message' => $this->str($sMessage)]);
        }
        // $this->aDebugMessages = [];
    }

    /**
     * Get the content type of the HTTP response
     *
     * @return string
     */
    public function getContentType(): string
    {
        return empty($this->sCharacterEncoding) ? $this->getResponse()->getContentType() :
            $this->getResponse()->getContentType() . '; charset="' . $this->sCharacterEncoding . '"';
    }

    /**
     * Get the JSON output of the response
     *
     * @return string
     */
    public function getOutput(): string
    {
        return $this->getResponse()->getOutput();
    }

    /**
     * Get the debug messages
     *
     * @return array
     */
    public function getDebugMessages(): array
    {
        return $this->aDebugMessages;
    }
}
