<?php

/**
 * CallableDirPlugin.php - Jaxon callable dir plugin
 *
 * This class registers directories containing user defined callable classes,
 * and generates client side javascript code.
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

namespace Jaxon\Plugin\Request\CallableClass;

use Jaxon\Jaxon;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\ComponentContainer;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\CallableRegistryInterface;
use Jaxon\Plugin\PluginInterface;

use function is_array;
use function is_dir;
use function is_string;
use function realpath;
use function rtrim;
use function str_replace;
use function trim;

class CallableDirPlugin implements PluginInterface, CallableRegistryInterface
{
    /**
     * The class constructor
     *
     * @param ComponentContainer $cdi
     * @param ComponentRegistry $xRegistry
     * @param Translator $xTranslator
     */
    public function __construct(protected ComponentContainer $cdi,
        protected ComponentRegistry $xRegistry, protected Translator $xTranslator)
    {}

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return Jaxon::CALLABLE_DIR;
    }

    /**
     * Check the directory
     *
     * @param string $sDirectory    The path of teh directory being registered
     *
     * @return string
     * @throws SetupException
     */
    private function checkDirectory(string $sDirectory): string
    {
        $sDirectory = rtrim(trim($sDirectory), '/\\');
        if(!is_dir($sDirectory))
        {
            throw new SetupException($this->xTranslator->trans('errors.objects.invalid-declaration'));
        }
        return realpath($sDirectory);
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function checkOptions(string $sCallable, $xOptions): array
    {
        if(is_string($xOptions))
        {
            $xOptions = ['namespace' => $xOptions];
        }
        if(!is_array($xOptions))
        {
            throw new SetupException($this->xTranslator->trans('errors.objects.invalid-declaration'));
        }
        // Check the directory
        $xOptions['directory'] = $this->checkDirectory($sCallable);
        // Check the namespace
        $sNamespace = $xOptions['namespace'] ?? '';
        if(!($xOptions['namespace'] = trim($sNamespace, ' \\')))
        {
            $xOptions['namespace'] = '';
        }

        // Change the keys in $xOptions to have "\" as separator
        $_aOptions = [];
        foreach($xOptions as $sName => $aOption)
        {
            $sName = trim(str_replace('.', '\\', $sName), ' \\');
            $_aOptions[$sName] = $aOption;
        }
        return $_aOptions;
    }

    /**
     * @inheritDoc
     */
    public function register(string $sType, string $sCallable, array $aOptions): bool
    {
        // The $sCallable var is not used here because the checkOptions() method copied it into the $aOptions array.
        if(($aOptions['namespace']))
        {
            $this->xRegistry->registerNamespace($aOptions['namespace'], $aOptions);
            return true;
        }
        $this->xRegistry->registerDirectory($aOptions['directory'], $aOptions);
        return true;
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function getCallable(string $sCallable): CallableObject|null
    {
        return $this->cdi->makeCallableObject($sCallable);
    }
}
