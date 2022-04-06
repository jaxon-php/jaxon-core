<?php

/**
 * AbstractDialogLibrary.php
 *
 * Base class for javascript dialog library adapters.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-dialogs
 */

namespace Jaxon\Ui\Dialog\Library;

use Jaxon\Plugin\Response\Dialog\DialogPlugin;

abstract class AbstractDialogLibrary implements DialogLibraryInterface
{
    use DialogLibraryTrait;

    /**
     * Add a client side plugin command to the response object
     *
     * @param array $aAttributes The attributes of the command
     * @param mixed $xData The data to be added to the command
     *
     * @return void
     */
    final public function addCommand(array $aAttributes, $xData)
    {
        $aAttributes['plg'] = $this->getName();
        $this->xResponse->addCommand($aAttributes, $xData);
    }

    /**
     * @inheritDoc
     */
    public function getUri(): string
    {
        return 'https://cdn.jaxon-php.org/libs';
    }

    /**
     * @inheritDoc
     */
    public function getSubdir(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getJs(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getCss(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getScript(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getReadyScript(): string
    {
        return '';
    }
}
