<?php

/**
 * CallableDir.php - Jaxon callable dir plugin
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

namespace Jaxon\Request\Plugin;

use Jaxon\Jaxon;
use Jaxon\Plugin\Request as RequestPlugin;
use Jaxon\Request\Support\CallableRegistry;
use Jaxon\Exception\SetupException;

use function is_string;
use function is_array;
use function rtrim;
use function trim;
use function is_dir;
use function realpath;

class CallableDir extends RequestPlugin
{
    use \Jaxon\Features\Translator;

    /**
     * The callable registrar
     *
     * @var CallableRegistry
     */
    protected $xRegistry;

    /**
     * The class constructor
     *
     * @param CallableRegistry        $xRegistry
     */
    public function __construct(CallableRegistry $xRegistry)
    {
        $this->xRegistry = $xRegistry;
    }

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
     * @param string        $sDirectory     The path of teh directory being registered
     *
     * @return string
     * @throws SetupException
     */
    private function checkDirectory(string $sDirectory): string
    {
        // if(!is_string($sDirectory))
        // {
        //     throw new \Jaxon\Exception\SetupException($this->trans('errors.objects.invalid-declaration'));
        // }
        $sDirectory = rtrim(trim($sDirectory), '/\\');
        if(!is_dir($sDirectory))
        {
            throw new SetupException($this->trans('errors.objects.invalid-declaration'));
        }
        return realpath($sDirectory);
    }

    /**
     * Check the options
     *
     * @param array|string  $aOptions       The associated options
     *
     * @return array
     * @throws SetupException
     */
    private function checkOptions($aOptions): array
    {
        if(is_string($aOptions))
        {
            $aOptions = ['namespace' => $aOptions];
        }
        if(!is_array($aOptions))
        {
            throw new SetupException($this->trans('errors.objects.invalid-declaration'));
        }

        // Change the keys in $aOptions to have "\" as separator
        $_aOptions = [];
        foreach($aOptions as $sName => $aOption)
        {
            $sName = trim(str_replace('.', '\\', $sName), ' \\');
            $_aOptions[$sName] = $aOption;
        }
        return $_aOptions;
    }

    /**
     * Register a callable class
     *
     * @param string $sType The type of request handler being registered
     * @param string $sDirectory The path of the directory being registered
     * @param array|string $aOptions The associated options
     *
     * @return bool
     * @throws SetupException
     */
    public function register(string $sType, string $sDirectory, $aOptions): bool
    {
        $sType = trim($sType);
        if($sType != $this->getName())
        {
            return false;
        }

        $sDirectory = $this->checkDirectory($sDirectory);

        $aOptions = $this->checkOptions($aOptions);
        $aOptions['directory'] = $sDirectory;

        $sNamespace = $aOptions['namespace'] ?? '';
        if(!($sNamespace = trim($sNamespace, ' \\')))
        {
            $sNamespace = '';
        }

        if(($sNamespace))
        {
            $this->xRegistry->addNamespace($sNamespace, $aOptions);
        }
        else
        {
            $this->xRegistry->addDirectory($sDirectory, $aOptions);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function canProcessRequest(): bool
    {
        return false;
    }

    /**
     * Process the incoming Jaxon request
     *
     * @return bool
     */
    public function processRequest(): bool
    {
        return false;
    }
}
