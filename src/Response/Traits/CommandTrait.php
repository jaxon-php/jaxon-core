<?php

namespace Jaxon\Response\Traits;

use Jaxon\Response\ResponseInterface;

use function array_merge;
use function count;

trait CommandTrait
{
    /**
     * The commands that will be sent to the browser in the response
     *
     * @var array
     */
    protected $aCommands = [];

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
     * Merge the commands with those in this <Response> object
     *
     * @param array $aCommands    The commands to merge
     * @param bool $bBefore    Add the new commands to the beginning of the list
     *
     * @return void
     */
    public function appendCommands(array $aCommands, bool $bBefore = false)
    {
        $this->aCommands = ($bBefore) ?
            array_merge($aCommands, $this->aCommands) :
            array_merge($this->aCommands, $aCommands);
    }

    /**
     * Merge the response commands with those in this <Response> object
     *
     * @param ResponseInterface $xResponse    The <Response> object
     * @param bool $bBefore    Add the new commands to the beginning of the list
     *
     * @return void
     */
    public function appendResponse(ResponseInterface $xResponse, bool $bBefore = false)
    {
        $this->appendCommands($xResponse->getCommands(), $bBefore);
    }
}
