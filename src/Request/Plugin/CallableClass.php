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
use Jaxon\CallableClass as UserCallableClass;
use Jaxon\Plugin\Request as RequestPlugin;
use Jaxon\Request\Support\CallableRepository;

class CallableClass extends RequestPlugin
{
    use \Jaxon\Features\Config;
    use \Jaxon\Features\Template;
    use \Jaxon\Features\Validator;
    use \Jaxon\Features\Translator;

    /**
     * The callable repository
     *
     * @var CallableRepository
     */
    protected $xRepository = null;

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

    /**
     * The class constructor
     *
     * @param CallableRepository        $xRepository
     */
    public function __construct(CallableRepository $xRepository)
    {
        $this->xRepository = $xRepository;

        if(!empty($_GET['jxncls']))
        {
            $this->sRequestedClass = trim($_GET['jxncls']);
        }
        if(!empty($_GET['jxnmthd']))
        {
            $this->sRequestedMethod = trim($_GET['jxnmthd']);
        }
        if(!empty($_POST['jxncls']))
        {
            $this->sRequestedClass = trim($_POST['jxncls']);
        }
        if(!empty($_POST['jxnmthd']))
        {
            $this->sRequestedMethod = trim($_POST['jxnmthd']);
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
     * Return the name of target function
     *
     * @return string
     */
    public function getTarget()
    {
        return ['class' => $this->sRequestedClass, 'method' => $this->sRequestedMethod];
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
        $sType = trim($sType);
        if($sType != $this->getName())
        {
            return false;
        }

        if(!is_string($sClassName))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }
        if(is_string($aOptions))
        {
            $aOptions = ['include' => $aOptions];
        }
        if(!is_array($aOptions))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }

        $sClassName = trim($sClassName);
        $this->xRepository->addClass($sClassName, $aOptions);

        return true;
    }

    /**
     * Generate a hash for the registered callable objects
     *
     * @return string
     */
    public function generateHash()
    {
        $this->xRepository->createCallableObjects();
        $aNamespaces = $this->xRepository->getNamespaces();
        $aCallableObjects = $this->xRepository->getCallableObjects();
        $sHash = '';

        foreach($aNamespaces as $sNamespace => $aOptions)
        {
            $sHash .= $sNamespace . $aOptions['separator'];
        }
        foreach($aCallableObjects as $sClassName => $xCallableObject)
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
        $this->xRepository->createCallableObjects();
        $aNamespaces = $this->xRepository->getNamespaces();
        $aCallableObjects = $this->xRepository->getCallableObjects();
        $aCallableOptions = $this->xRepository->getCallableOptions();
        $sPrefix = $this->getOption('core.prefix.class');
        $aJsClasses = [];
        $sCode = '';

        $xCallableClass = new \ReflectionClass(UserCallableClass::class);
        $aCallableMethods = [];
        foreach($xCallableClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $aCallableMethods[] = $xMethod->getName();
        }

        foreach(array_keys($aNamespaces) as $sNamespace)
        {
            $offset = 0;
            $sJsNamespace = str_replace('\\', '.', $sNamespace);
            $sJsNamespace .= '.Null'; // This is a sentinel. The last token is not processed in the while loop.
            while(($dotPosition = strpos($sJsNamespace, '.', $offset)) !== false)
            {
                $sJsClass = substr($sJsNamespace, 0, $dotPosition);
                // Generate code for this object
                if(!key_exists($sJsClass, $aJsClasses))
                {
                    $sCode .= "$sPrefix$sJsClass = {};\n";
                    $aJsClasses[$sJsClass] = $sJsClass;
                }
                $offset = $dotPosition + 1;
            }
        }

        foreach($aCallableObjects as $sClassName => $xCallableObject)
        {
            $aConfig = $aCallableOptions[$sClassName];
            $aCommonConfig = key_exists('*', $aConfig) ? $aConfig['*'] : [];

            $aProtectedMethods = is_subclass_of($sClassName, UserCallableClass::class) ? $aCallableMethods : [];
            $aMethods = [];
            foreach($xCallableObject->getMethods() as $sMethodName)
            {
                // Don't export methods of the CallableClass class
                if(in_array($sMethodName, $aProtectedMethods))
                {
                    continue;
                }
                // Specific options for this method
                $aMethodConfig = key_exists($sMethodName, $aConfig) ?
                    array_merge($aCommonConfig, $aConfig[$sMethodName]) : $aCommonConfig;
                $aMethods[] = [
                    'name' => $sMethodName,
                    'config' => $aMethodConfig,
                ];
            }

            $sCode .= $this->render('jaxon::support/object.js', [
                'sPrefix' => $sPrefix,
                'sClass' => $xCallableObject->getJsName(),
                'aMethods' => $aMethods,
            ]);
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
        if(($this->sRequestedClass !== null && !$this->validateClass($this->sRequestedClass)) ||
            ($this->sRequestedMethod !== null && !$this->validateMethod($this->sRequestedMethod)))
        {
            $this->sRequestedClass = null;
            $this->sRequestedMethod = null;
        }
        return ($this->sRequestedClass !== null && $this->sRequestedMethod !== null);
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

        // Find the requested method
        $xCallableObject = $this->xRepository->getCallableObject($this->sRequestedClass);
        if(!$xCallableObject || !$xCallableObject->hasMethod($this->sRequestedMethod))
        {
            // Unable to find the requested object or method
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid',
                ['class' => $this->sRequestedClass, 'method' => $this->sRequestedMethod]));
        }

        // Call the requested method
        $di = jaxon()->di();
        $aArgs = $di->getRequestHandler()->processArguments();
        $xResponse = $xCallableObject->call($this->sRequestedMethod, $aArgs);
        if(($xResponse))
        {
            $di->getResponseManager()->append($xResponse);
        }
        return true;
    }
}
