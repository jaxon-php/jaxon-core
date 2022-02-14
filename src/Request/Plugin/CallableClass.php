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
use Jaxon\Request\Support\CallableObject;
use Jaxon\Request\Support\CallableRegistry;
use Jaxon\Request\Support\CallableRepository;
use Jaxon\Request\Target;

class CallableClass extends RequestPlugin
{
    use \Jaxon\Features\Config;
    use \Jaxon\Features\Template;
    use \Jaxon\Features\Validator;
    use \Jaxon\Features\Translator;

    /**
     * The callable registry
     *
     * @var CallableRegistry
     */
    protected $xRegistry;

    /**
     * The callable repository
     *
     * @var CallableRepository
     */
    protected $xRepository;

    /**
     * The value of the class parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedClass = '';

    /**
     * The value of the method parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedMethod = '';

    /**
     * The class constructor
     *
     * @param CallableRegistry      $xRegistry      The callable class registry
     * @param CallableRepository    $xRepository    The callable object repository
     */
    public function __construct(CallableRegistry $xRegistry, CallableRepository $xRepository)
    {
        $this->xRegistry = $xRegistry;
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
     * @inheritDoc
     */
    public function getName()
    {
        return Jaxon::CALLABLE_CLASS;
    }

    /**
     * @inheritDoc
     */
    public function getTarget()
    {
        if(!$this->sRequestedClass || !$this->sRequestedMethod)
        {
            return null;
        }
        return Target::makeClass($this->sRequestedClass, $this->sRequestedMethod);
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

        $this->xRepository->addClass(trim($sClassName), $aOptions);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getHash()
    {
        $this->xRegistry->registerCallableClasses();
        $aNamespaces = $this->xRepository->getNamespaces();
        $aClasses = $this->xRepository->getClasses();
        $sHash = '';

        foreach($aNamespaces as $sNamespace => $aOptions)
        {
            $sHash .= $sNamespace . $aOptions['separator'];
        }
        foreach($aClasses as $sClassName => $aOptions)
        {
            $sHash .= $sClassName . $aOptions['timestamp'];
        }

        return md5($sHash);
    }

    /**
     * Generate client side javascript code for namespaces
     *
     * @return string
     */
    private function getNamespacesScript()
    {
        $sCode = '';
        $sPrefix = $this->getOption('core.prefix.class');
        $aJsClasses = [];
        $aNamespaces = array_keys($this->xRepository->getNamespaces());
        foreach($aNamespaces as $sNamespace)
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
        return $sCode;
    }

    /**
     * Generate client side javascript code for a callable class
     *
     * @param string            $sClassName         The class name
     * @param CallableObject    $xCallableObject    The corresponding callable object
     * @param array             $aProtectedMethods  The protected methods
     *
     * @return string
     */
    private function getCallableScript($sClassName, CallableObject $xCallableObject, array $aProtectedMethods)
    {
        $aCallableOptions = $this->xRepository->getCallableOptions();
        $aConfig = $aCallableOptions[$sClassName];

        // Convert an option to string, to be displayed in the js script template.
        $fConvertOption = function($xOption) {
            return is_array($xOption) ? json_encode($xOption) : $xOption;
        };
        $aCommonConfig = isset($aConfig['*']) ? array_map($fConvertOption, $aConfig['*']) : [];

        $_aProtectedMethods = is_subclass_of($sClassName, UserCallableClass::class) ? $aProtectedMethods : [];
        $aMethods = [];
        foreach($xCallableObject->getMethods() as $sMethodName)
        {
            // Don't export methods of the CallableClass class
            if(in_array($sMethodName, $_aProtectedMethods))
            {
                continue;
            }
            // Specific options for this method
            $aMethodConfig = isset($aConfig[$sMethodName]) ?
                array_map($fConvertOption, $aConfig[$sMethodName]) : [];
            $aMethods[] = [
                'name' => $sMethodName,
                'config' => array_merge($aCommonConfig, $aMethodConfig),
            ];
        }

        $sPrefix = $this->getOption('core.prefix.class');
        return $this->render('jaxon::support/object.js', [
            'sPrefix' => $sPrefix,
            'sClass' => $xCallableObject->getJsName(),
            'aMethods' => $aMethods,
        ]);
    }

    /**
     * Generate client side javascript code for the registered callable objects
     *
     * @return string
     */
    public function getScript()
    {
        $this->xRegistry->createCallableObjects();

        // The methods of the \Jaxon\CallableClass class must not be exported
        $xCallableClass = new \ReflectionClass(UserCallableClass::class);
        $aProtectedMethods = [];
        foreach($xCallableClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $aProtectedMethods[] = $xMethod->getName();
        }

        $sCode = $this->getNamespacesScript();

        $aCallableObjects = $this->xRepository->getCallableObjects();
        // Sort the options by key length asc
        uksort($aCallableObjects, function($name1, $name2) {
            return strlen($name1) - strlen($name2);
        });
        foreach($aCallableObjects as $sClassName => $xCallableObject)
        {
            $sCode .= $this->getCallableScript($sClassName, $xCallableObject, $aProtectedMethods);
        }

        return $sCode;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function processRequest()
    {
        if(!$this->canProcessRequest())
        {
            return false;
        }

        // Find the requested method
        $xCallableObject = $this->xRegistry->getCallableObject($this->sRequestedClass);
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
