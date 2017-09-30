<?php

/**
 * Factory.php - Trait for Jaxon Request Factory
 *
 * Make functions of the Jaxon Request Factory class available to Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Traits;

trait Factory
{
    /**
     * The \Jaxon\Request\Support\CallableObject instance associated to the Jaxon object using this trait
     *
     * @var \Jaxon\Request\Support\CallableObject
     */
    private $xJaxonCallable = null;

    /**
     * Set the associated \Jaxon\Request\Support\CallableObject instance
     *
     * @param object        $xJaxonCallable            The \Jaxon\Request\Support\CallableO object instance
     *
     * @return void
     */
    public function setJaxonCallable($xJaxonCallable)
    {
        $this->xJaxonCallable = $xJaxonCallable;
    }

    /**
     * Get the Jaxon class name
     *
     * This is the name to be used in Jaxon javascript calls.
     *
     * @return string        The Jaxon class name
     */
    public function getJaxonClassName()
    {
        if(!$this->xJaxonCallable)
        {
            // Make the Jaxon class name for a class without an associated callable
            // !! Warning !! This can happen only if this object is not registered with the Jaxon libary
            $xReflectionClass = new \ReflectionClass(get_class($this));
            // Return the class name without the namespace.
            return $xReflectionClass->getShortName();
        }
        return $this->xJaxonCallable->getJsName();
    }

    /**
     * Get the Jaxon class namespace
     *
     * @return string        The Jaxon class namespace
     */
    public function getJaxonNamespace()
    {
        if(!$this->xJaxonCallable)
        {
            // Return an empty string.
            return '';
        }
        return $this->xJaxonCallable->getNamespace();
    }

    /**
     * Get the Jaxon class path
     *
     * @return string        The Jaxon class path
     */
    public function getJaxonClassPath()
    {
        if(!$this->xJaxonCallable)
        {
            // Return an empty string.
            return '';
        }
        return $this->xJaxonCallable->getPath();
    }

    /**
     * Return the javascript call to an Jaxon object method
     *
     * @param string         $sMethod           The method (without class) name
     * @param ...            $xParams           The parameters of the method
     *
     * @return object
     */
    public function call($sMethod)
    {
        $sMethod = (string)$sMethod;
        $aArgs = func_get_args();
        // Prepend the class name to the method name
        $aArgs[0] = $this->getJaxonClassName() . '.' . $sMethod;
        // Make the request
        return call_user_func_array('\Jaxon\Request\Factory::call', $aArgs);
    }

    /**
     * Make the pagination links for a registered Jaxon class method
     *
     * @param integer $nItemsTotal the total number of items
     * @param integer $nItemsPerPage the number of items per page
     * @param integer $nCurrentPage the current page
     * @param string  $sMethod the name of the method
     * @param ... $parameters the parameters of the method
     *
     * @return string the pagination links
     */
    public function paginate($nItemsTotal, $nItemsPerPage, $nCurrentPage, $sMethod)
    {
        $sMethod = (string)$sMethod;
        $aArgs = func_get_args();
        // Prepend the class name to the method name
        $aArgs[3] = $this->getJaxonClassName() . '.' . $sMethod;
        // Make the request
        return call_user_func_array('\Jaxon\Request\Factory::paginate', $aArgs);
    }
}
