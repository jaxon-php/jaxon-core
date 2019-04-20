<?php

/**
 * CallableObject.php - Jaxon callable object plugin
 *
 * This class registers user defined callable objects, generates client side javascript code,
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

class CallableObject extends RequestPlugin
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Validator;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * The registered callable objects
     *
     * @var array
     */
    protected $aCallableObjects;

    /**
     * The classpaths of the registered callable objects
     *
     * @var array
     */
    protected $aClassPaths;

    /**
     * The value of the class parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedClass;

    /**
     * The value of the method parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedMethod;

    public function __construct()
    {
        $this->aCallableObjects = [];
        $this->aClassPaths = [];

        $this->sRequestedClass = null;
        $this->sRequestedMethod = null;

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
        return Jaxon::CALLABLE_OBJECT;
    }

    /**
     * Register a user defined callable object
     *
     * @param array         $aArgs                An array containing the callable object specification
     *
     * @return array
     */
    public function register($aArgs)
    {
        if(count($aArgs) < 2)
        {
            return false;
        }

        $sType = $aArgs[0];
        if($sType != Jaxon::CALLABLE_OBJECT)
        {
            return false;
        }

        $sCallableObject = $aArgs[1];
        if(!is_string($sCallableObject) || !class_exists($sCallableObject))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }
        $sCallableObject = trim($sCallableObject, '\\');
        $this->aCallableObjects[] = $sCallableObject;

        $aOptions = count($aArgs) > 2 ? $aArgs[2] : [];
        if(is_string($aOptions))
        {
            $aOptions = ['namespace' => $aOptions];
        }
        if(!is_array($aOptions))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }

        // Save the classpath and the separator in this class
        if(key_exists('*', $aOptions) && is_array($aOptions['*']))
        {
            $_aOptions = $aOptions['*'];
            $sSeparator = '.';
            if(key_exists('separator', $_aOptions))
            {
                $sSeparator = trim($_aOptions['separator']);
            }
            if(!in_array($sSeparator, ['.', '_']))
            {
                $sSeparator = '.';
            }
            $_aOptions['separator'] = $sSeparator;

            if(array_key_exists('classpath', $_aOptions))
            {
                $_aOptions['classpath'] = trim($_aOptions['classpath'], ' \\._');
                // Save classpath with "\" in the parameters
                $_aOptions['classpath'] = str_replace(['.', '_'], ['\\', '\\'], $_aOptions['classpath']);
                // Save classpath with separator locally
                $this->aClassPaths[] = str_replace('\\', $sSeparator, $_aOptions['classpath']);
            }
        }

        jaxon()->di()->set($sUserFunction, function() use($sCallableObject, $aOptions) {
            $xCallableObject = new \Jaxon\Request\Support\CallableObject($sCallableObject);

            foreach($aOptions as $sMethod => $aValue)
            {
                foreach($aValue as $sName => $sValue)
                {
                    $xCallableObject->configure($sMethod, $sName, $sValue);
                }
            }

            return $xCallableObject;
        });

        return true;
    }

    /**
     * Generate a hash for the registered callable objects
     *
     * @return string
     */
    public function generateHash()
    {
        $di = jaxon()->di();
        $sHash = '';
        foreach($this->aCallableObjects as $sName)
        {
            $xCallableObject = $di->get($sName);
            $sHash .= $sName . implode('|', $xCallableObject->getMethods());
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
        $sJaxonPrefix = $this->getOption('core.prefix.class');
        // Generate code for javascript objects declaration
        $code = '';
        $classes = [];
        foreach($this->aClassPaths as $sClassPath)
        {
            $offset = 0;
            $sClassPath .= '.Null'; // This is a sentinel. The last token is not processed in the while loop.
            while(($dotPosition = strpos($sClassPath, '.', $offset)) !== false)
            {
                $class = substr($sClassPath, 0, $dotPosition);
                // Generate code for this object
                if(!array_key_exists($class, $classes))
                {
                    $code .= "$sJaxonPrefix$class = {};\n";
                    $classes[$class] = $class;
                }
                $offset = $dotPosition + 1;
            }
        }
        // Generate code for javascript methods
        $di = jaxon()->di();
        foreach($this->aCallableObjects as $sName)
        {
            $xCallableObject = $di->get($sName);
            $code .= $xCallableObject->getScript();
        }
        return $code;
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
        return ($this->sRequestedClass != null && $this->sRequestedMethod != null);
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
        // Register an instance of the requested class, if it isn't yet
        if(!key_exists($sClassName, $this->aCallableObjects))
        {
            $this->getPluginManager()->registerClass($sClassName);
        }
        return key_exists($sClassName, $this->aCallableObjects) ? jaxon()->di()->get($sClassName) : null;
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
}
