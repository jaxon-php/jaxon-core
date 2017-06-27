<?php

/**
 * View.php - Trait for view functions
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Traits;

use Jaxon\Utils\Container;

trait View
{
    /**
     * Set the view renderers data
     *
     * @param array                $aRenderers          Array of renderer names with namespace as key
     *
     * @return void
     */
    public function initViewRenderers($aRenderers)
    {
        return Container::getInstance()->initViewRenderers($aRenderers);
    }
    
    /**
     * Set the view namespaces data
     *
     * @param array                $aNamespaces         Array of namespaces with renderer name as key
     *
     * @return void
     */
    public function initViewNamespaces($aNamespaces, $sDefaultNamespace)
    {
        return Container::getInstance()->initViewNamespaces($aNamespaces, $sDefaultNamespace);
    }
    
    /**
     * Get the view renderer facade
     *
     * @param string                $sId                The unique identifier of the view renderer
     *
     * @return object        The view renderer
     */
    public function getViewRenderer($sId = '')
    {
        return Container::getInstance()->getViewRenderer($sId);
    }
    
    /**
     * Add a view renderer with an id
     *
     * @param string                $sId                The unique identifier of the view renderer
     * @param Closure               $xClosure           A closure to create the view instance
     *
     * @return void
     */
    public function addViewRenderer($sId, $xClosure)
    {
        Container::getInstance()->addViewRenderer($sId, $xClosure);
    }
}
