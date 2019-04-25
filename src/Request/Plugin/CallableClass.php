<?php

/**
 * CallableClass.php - Jaxon callable class plugin
 *
 * This class registers user defined callable classes, generates client side javascript code,
 * and calls their methods on user request
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

class CallableClass extends RequestPlugin
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Validator;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * The classes of the registered callable objects
     *
     * @var array
     */
    protected $aClassOptions = [];

    /**
     * The registered callable objects
     *
     * @var array
     */
    protected $aCallableObjects = [];

    /**
     * The value of the class parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedClass = null;

    /**
     * The value of the method parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedMethod = null;

    public function __construct()
    {
        if(!empty($_GET['jxncls']))
        {
            $this->sRequestedClass = $_GET['jxncls'];
        }
        if(!empty($_GET['jxnmthd']))
        {
            $this->sRequestedMethod = $_GET['jxnmthd'];
        }
        if(!empty($_POST['jxncls']))
        {
            $this->sRequestedClass = $_POST['jxncls'];
        }
        if(!empty($_POST['jxnmthd']))
        {
            $this->sRequestedMethod = $_POST['jxnmthd'];
        }
    }

    /**
     * Return the name of this plugin
     *
     * @return string
     */
    public function getName()
    {
        return Jaxon::CALLABLE_CLASS;
    }

    /**
     * Register a callable class
     *
     * @param string        $sType          The type of request handler being registered
     * @param string        $sClassName     The name of the class being registered
     * @param array|string  $aOptions       The associated options
     *
     * @return boolean
     */
    public function register($sType, $sClassName, $aOptions)
    {
        if($sType != $this->getName())
        {
            return false;
        }

        if(!is_string($sClassName))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }
        if(!is_array($aOptions))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }

        $sClassName = trim($sClassName, '\\');
        $this->aClassOptions[$sClassName] = $aOptions;

        return true;
    }

    /**
     * Find a callable object by class name
     *
     * @param string        $sClassName            The class name of the callable object
     *
     * @return object
     */
    public function getCallableObject($sClassName)
    {
        // Replace all separators ('.' and '_') with antislashes, and remove the antislashes
        // at the beginning and the end of the class name.
        $sClassName = trim(str_replace(['.', '_'], ['\\', '\\'], (string)$sClassName), '\\');

        if(!key_exists($sClassName, $this->aClassOptions))
        {
            return null; // Class not registered
        }

        if(key_exists($sClassName, $this->aCallableObjects))
        {
            return $this->aCallableObjects[$sClassName];
        }

        // Create the callable object
        $xCallableObject = new \Jaxon\Request\Support\CallableObject($sClassName);
        $aOptions = $this->aClassOptions[$sClassName];
        foreach($aOptions as $sMethod => $aValue)
        {
            foreach($aValue as $sName => $sValue)
            {
                $xCallableObject->configure($sMethod, $sName, $sValue);
            }
        }

        // Make sure the registered class exists
        // We need to check this after the callable object is configured
        // to take the 'include' option into account.
        if(!class_exists($sClassName))
        {
            return null;
        }

        $this->aCallableObjects[$sClassName] = $xCallableObject;
        // jaxon()->di()->set($sClassName, $xCallableObject);
        // Register the request factory for this callable object
        jaxon()->di()->set($sClassName . '_Factory_Rq', function ($di) use ($sClassName) {
            $xCallableObject = $this->aCallableObjects[$sClassName];
            return new \Jaxon\Factory\Request\Portable($xCallableObject);
        });
        // Register the paginator factory for this callable object
        jaxon()->di()->set($sClassName . '_Factory_Pg', function ($di) use ($sClassName) {
            $xCallableObject = $this->aCallableObjects[$sClassName];
            return new \Jaxon\Factory\Request\Paginator($xCallableObject);
        });

        return $xCallableObject;
    }

    /**
     * Find a user registered callable object by class name
     *
     * @param string        $sClassName            The class name of the callable object
     *
     * @return object
     */
    public function getRegisteredObject($sClassName)
    {
        // Get the corresponding callable object
        $xCallableObject = $this->getCallableObject($sClassName);
        return ($xCallableObject) ? $xCallableObject->getRegisteredObject() : null;
    }

    /**
     * Create callable objects for all registered namespaces
     *
     * @return void
     */
    private function createCallableObjects()
    {
        foreach(array_keys($this->aClassOptions) as $sClassName)
        {
            $this->getCallableObject($sClassName);
        }
    }

    /**
     * Generate a hash for the registered callable objects
     *
     * @return string
     */
    public function generateHash()
    {
        $this->createCallableObjects();

        $sHash = '';
        foreach($this->aCallableObjects as $sClassName => $xCallableObject)
        {
            $sHash .= $sClassName . implode('|', $xCallableObject->getMethods());
        }

        return md5($sHash);
    }

    /**
     * Generate client side javascript code for the registered callable objects
     *
     * @return string
     */
    public function getScript()
    {
        $this->createCallableObjects();

        // Generate code for javascript objects declaration
        $sJaxonPrefix = $this->getOption('core.prefix.class');
        $aJsClasses = [];
        $sCode = '';
        foreach($this->aClassOptions as $sClassName => $aOptions)
        {
            if(key_exists('separator', $aOptions) && $aOptions['separator'] != '.')
            {
                continue;
            }
            $offset = 0;
            $sJsClasses = str_replace('\\', '.', $sClassName);
            while(($dotPosition = strpos($sJsClasses, '.', $offset)) !== false)
            {
                $sJsClass = substr($sJsClasses, 0, $dotPosition);
                // Generate code for this object
                if(!key_exists($sJsClass, $aJsClasses))
                {
                    $sCode .= "$sJaxonPrefix$sJsClass = {};\n";
                    $aJsClasses[$sJsClass] = $sJsClass;
                }
                $offset = $dotPosition + 1;
            }
        }
        foreach($this->aCallableObjects as $xCallableObject)
        {
            $sCode .= $xCallableObject->getScript();
        }

        return $sCode;
    }

    /**
     * Check if this plugin can process the incoming Jaxon request
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        // Check the validity of the class name
        if(($this->sRequestedClass) && !$this->validateClass($this->sRequestedClass))
        {
            $this->sRequestedClass = null;
            $this->sRequestedMethod = null;
        }
        // Check the validity of the method name
        if(($this->sRequestedMethod) && !$this->validateMethod($this->sRequestedMethod))
        {
            $this->sRequestedClass = null;
            $this->sRequestedMethod = null;
        }
        return ($this->sRequestedClass != null && $this->sRequestedMethod != null &&
            key_exists($this->sRequestedClass, $this->aCallableObjects));
    }

    /**
     * Process the incoming Jaxon request
     *
     * @return boolean
     */
    public function processRequest()
    {
        if(!$this->canProcessRequest())
        {
            return false;
        }

        $aArgs = $this->getRequestManager()->process();

        // Find the requested method
        $xCallableObject = $this->getCallableObject($this->sRequestedClass);
        if(!$xCallableObject || !$xCallableObject->hasMethod($this->sRequestedMethod))
        {
            // Unable to find the requested object or method
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid',
                ['class' => $this->sRequestedClass, 'method' => $this->sRequestedMethod]));
        }

        // Call the requested method
        $xCallableObject->call($this->sRequestedMethod, $aArgs);
        return true;
    }
}
