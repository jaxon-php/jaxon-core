<?php

namespace Jaxon\Request\Factory;

/**
 * RequestFactory.php
 *
 * Create Jaxon client side requests, which will generate the client script necessary
 * to invoke a jaxon request from the browser to registered objects.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Dialog\Library\DialogLibraryManager;
use Jaxon\Request\Call\Call;
use Jaxon\Request\Call\Paginator;

use function array_shift;
use function func_get_args;

class RequestFactory
{
    /**
     * @var string
     */
    protected $sPrefix;

    /**
     * @var bool
     */
    protected $bNoPrefix = false;

    /**
     * @var DialogLibraryManager
     */
    protected $xDialogLibraryManager;

    /**
     * @var Paginator
     */
    protected $xPaginator;

    /**
     * The class constructor
     *
     * @param string $sPrefix
     * @param DialogLibraryManager $xDialogLibraryManager
     * @param Paginator $xPaginator
     */
    public function __construct(string $sPrefix, DialogLibraryManager $xDialogLibraryManager, Paginator $xPaginator)
    {
        $this->sPrefix = $sPrefix;
        $this->xDialogLibraryManager = $xDialogLibraryManager;
        $this->xPaginator = $xPaginator;
    }

    /**
     * @param bool $bNoPrefix
     *
     * @return RequestFactory
     */
    public function noPrefix(bool $bNoPrefix): RequestFactory
    {
        $this->bNoPrefix = $bNoPrefix;
        return $this;
    }

    /**
     * Generate the javascript code for a call to a given method
     *
     * @param string $sFunction
     * @param array $aArguments
     *
     * @return Call
     */
    public function __call(string $sFunction, array $aArguments): Call
    {
        // Make the request
        $sPrefix = $this->bNoPrefix ? '' : $this->sPrefix;
        $xCall = new Call($sPrefix . $sFunction, $this->xDialogLibraryManager, $this->xPaginator);
        $xCall->addParameters($aArguments);
        return $xCall;
    }

    /**
     * Return the javascript call to a Jaxon function or object method
     *
     * @param string $sFunction    The function or method (without class) name
     *
     * @return Call
     */
    public function call(string $sFunction): Call
    {
        $aArguments = func_get_args();
        // Remove the function name from the arguments array.
        array_shift($aArguments);
        // Make the request
        return $this->__call($sFunction, $aArguments);
    }
}
