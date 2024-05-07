<?php

namespace Jaxon\Response\Traits;

use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Response\ResponseInterface;
use JsonSerializable;

use function array_filter;
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
     * Convert to string
     *
     * @param mixed $xData
     *
     * @return string
     */
    abstract protected function str($xData): string;

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

    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param string $sName    The command name
     * @param array|JsonSerializable $aOptions    The command options
     *
     * @return ResponseInterface
     */
    public function addCommand(string $sName, array|JsonSerializable $aOptions): ResponseInterface
    {
        $this->aCommands[] = [
            'cmd' => $this->str($sName),
            'options' => $aOptions,
        ];
        return $this;
    }

    /**
     * Add a response command that is generated by a plugin
     *
     * @param ResponsePlugin $xPlugin    The plugin object
     * @param string $sName    The command name
     * @param array|JsonSerializable $aOptions    The command options
     *
     * @return ResponseInterface
     */
    public function addPluginCommand(ResponsePlugin $xPlugin, string $sName,
        array|JsonSerializable $aOptions): ResponseInterface
    {
        $this->aCommands[] = [
            'cmd' => $this->str($sName),
            'plg' => $xPlugin->getName(),
            'options' => $aOptions,
        ];
        return $this;
    }

    /**
     * Add a response command to the array of commands that will be sent to the browser
     *
     * @param string $sName    The command name
     * @param array|JsonSerializable $aOptions    The command options
     * @param bool $bRemoveEmpty    If true, remove empty options
     *
     * @return ResponseInterface
     */
    protected function _addCommand(string $sName, array|JsonSerializable $aOptions,
        bool $bRemoveEmpty = false): ResponseInterface
    {
        if($bRemoveEmpty)
        {
            $aOptions = array_filter($aOptions, function($xOption) {
                return $xOption === '';
            });
        }
        return $this->addCommand($this->str($sName), $aOptions);
    }
}
