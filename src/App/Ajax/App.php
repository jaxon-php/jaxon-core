<?php

/**
 * App.php
 *
 * Jaxon application
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Ajax;

use Jaxon\Exception\SetupException;

use function file_exists;
use function is_array;

class App extends AbstractApp
{
    use Traits\SendResponseTrait;

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function setup(string $sConfigFile = ''): void
    {
        if(!file_exists($sConfigFile))
        {
            throw new SetupException($this->translator()
                ->trans('errors.file.access', ['path' => $sConfigFile]));
        }

        // Read the config options.
        $aOptions = $this->config()->read($sConfigFile);
        $aLibOptions = $aOptions['lib'] ?? [];
        $aAppOptions = $aOptions['app'] ?? [];
        if(!is_array($aLibOptions) || !is_array($aAppOptions))
        {
            throw new SetupException($sMessage = $this->translator()
                ->trans('errors.file.content', ['path' => $sConfigFile]));
        }

        // The bootstrap set this to false. It needs to be changed.
        if(!isset($aLibOptions['core']['response']['send']))
        {
            $aLibOptions['core']['response']['send'] = true;
        }

        $this->bootstrap()
            ->lib($aLibOptions)
            ->app($aAppOptions)
            ->setup();
    }
}
