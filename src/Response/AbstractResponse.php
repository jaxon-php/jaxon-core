<?php

/**
 * Response.php - The Jaxon Response
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

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use JsonSerializable;

use function trim;

abstract class AbstractResponse
{
    /**
     * @var ResponseManager
     */
    protected $xManager;

    /**
     * @var Psr17Factory
     */
    protected $xPsr17Factory;

    /**
     * @var int
     */
    protected $nCommandCount = 0;

    /**
     * The constructor
     *
     * @param ResponseManager $xManager
     * @param Psr17Factory $xPsr17Factory
     */
    public function __construct(ResponseManager $xManager, Psr17Factory $xPsr17Factory)
    {
        $this->xPsr17Factory = $xPsr17Factory;
        $this->xManager = $xManager;
    }

    /**
     * @inheritDoc
     */
    abstract public function getContentType(): string;

    /**
     * @inheritDoc
     */
    abstract public function getOutput(): string;

    /**
     * Convert this response to a PSR7 response object
     *
     * @return PsrResponseInterface
     */
    abstract public function toPsr(): PsrResponseInterface;

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
     * Get the error message
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->xManager->getErrorMessage();
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
        $this->nCommandCount++;
        $this->xManager->addCommand($sName, $aArgs, $bRemoveEmpty);
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
        $this->nCommandCount++;
        $this->xManager->insertCommand($sName, $aArgs, $nBefore, $bRemoveEmpty);
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
        $this->xManager->setOption($sName, $xValue);
    }

    /**
     * Get the commands in the response
     *
     * @return array
     */
    public function getCommands(): array
    {
        return $this->xManager->getCommands();
    }

    /**
     * Get the number of commands in the response
     *
     * @return int
     */
    public function getCommandCount(): int
    {
        return $this->xManager->getCommandCount();
    }

    /**
     * Clear all the commands already added to the response
     *
     * @return void
     */
    public function clearCommands()
    {
        $this->nCommandCount = 0;
        $this->xManager->clearCommands();
    }
}
