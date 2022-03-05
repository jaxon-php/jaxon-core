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
use Jaxon\Request\Handler\Handler as RequestHandler;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\CallableClass as UserCallableClass;
use Jaxon\Plugin\Request as RequestPlugin;
use Jaxon\Request\Support\CallableObject;
use Jaxon\Request\Support\CallableRegistry;
use Jaxon\Request\Support\CallableRepository;
use Jaxon\Request\Target;
use Jaxon\Request\Validator;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Template\Engine as TemplateEngine;
use Jaxon\Utils\Translation\Translator;

use function trim;
use function strlen;
use function is_string;
use function is_array;
use function in_array;
use function uksort;
use function md5;
use function array_map;
use function array_merge;
use function is_subclass_of;

class CallableClass extends RequestPlugin
{
    /**
     * @var Config
     */
    protected $xConfig;

    /**
     * The request handler
     *
     * @var RequestHandler
     */
    protected $xRequestHandler;

    /**
     * The response manager
     *
     * @var ResponseManager
     */
    protected $xResponseManager;

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
     * The request data validator
     *
     * @var Validator
     */
    protected $xValidator;

    /**
     * @var TemplateEngine
     */
    protected $xTemplateEngine;

    /**
     * @var Translator
     */
    protected $xTranslator;

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
     * @param Config                $xConfig
     * @param RequestHandler        $xRequestHandler
     * @param ResponseManager       $xResponseManager
     * @param CallableRegistry      $xRegistry      The callable class registry
     * @param CallableRepository    $xRepository    The callable object repository
     * @param TemplateEngine        $xTemplateEngine
     * @param Translator            $xTranslator
     * @param Validator             $xValidator
     */
    public function __construct(Config $xConfig, RequestHandler $xRequestHandler,
        ResponseManager $xResponseManager, CallableRegistry $xRegistry, CallableRepository $xRepository,
        TemplateEngine $xTemplateEngine, Translator $xTranslator, Validator $xValidator)
    {
        $this->xConfig = $xConfig;
        $this->xRequestHandler = $xRequestHandler;
        $this->xResponseManager = $xResponseManager;
        $this->xRegistry = $xRegistry;
        $this->xRepository = $xRepository;
        $this->xTemplateEngine = $xTemplateEngine;
        $this->xTranslator = $xTranslator;
        $this->xValidator = $xValidator;

        if(isset($_GET['jxncls']))
        {
            $this->sRequestedClass = trim($_GET['jxncls']);
        }
        if(isset($_GET['jxnmthd']))
        {
            $this->sRequestedMethod = trim($_GET['jxnmthd']);
        }
        if(isset($_POST['jxncls']))
        {
            $this->sRequestedClass = trim($_POST['jxncls']);
        }
        if(isset($_POST['jxnmthd']))
        {
            $this->sRequestedMethod = trim($_POST['jxnmthd']);
        }
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return Jaxon::CALLABLE_CLASS;
    }

    /**
     * @inheritDoc
     */
    public function getTarget(): ?Target
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
     * @param string $sType The type of request handler being registered
     * @param string $sClassName The name of the class being registered
     * @param array|string $aOptions The associated options
     *
     * @return bool
     * @throws SetupException
     */
    public function register(string $sType, string $sClassName, $aOptions): bool
    {
        $sType = trim($sType);
        if($sType != $this->getName())
        {
            return false;
        }

        // Todo: validate function name
        /*if(!is_string($sClassName))
        {
            throw new \Jaxon\Exception\SetupException($this->xTranslator->trans('errors.objects.invalid-declaration'));
        }*/
        if(is_string($aOptions))
        {
            $aOptions = ['include' => $aOptions];
        }
        if(!is_array($aOptions))
        {
            throw new SetupException($this->xTranslator->trans('errors.objects.invalid-declaration'));
        }

        $this->xRepository->addClass(trim($sClassName), $aOptions);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        $this->xRegistry->parseCallableClasses();
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
    private function getNamespacesScript(): string
    {
        $sCode = '';
        $sPrefix = $this->xConfig->getOption('core.prefix.class');
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
                if(!isset($aJsClasses[$sJsClass]))
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
     * @param CallableObject    $xCallableObject    The corresponding callable object
     * @param string            $sClassName         The class name
     * @param array             $aProtectedMethods  The protected methods
     *
     * @return string
     */
    private function getCallableScript(CallableObject $xCallableObject, string $sClassName, array $aProtectedMethods): string
    {
        $aConfig = $xCallableObject->getOptions();

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

        $sPrefix = $this->xConfig->getOption('core.prefix.class');
        return $this->xTemplateEngine->render('jaxon::support/object.js', [
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
    public function getScript(): string
    {
        $this->xRegistry->registerCallableObjects();

        // The methods of the \Jaxon\CallableClass class must not be exported
        $xCallableClass = new \ReflectionClass(UserCallableClass::class);
        $aProtectedMethods = [];
        foreach($xCallableClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $aProtectedMethods[] = $xMethod->getName();
        }

        $sCode = $this->getNamespacesScript();

        $aClassNames = $this->xRepository->getClassNames();
        // Sort the options by key length asc
        uksort($aClassNames, function($name1, $name2) {
            return strlen($name1) - strlen($name2);
        });
        foreach($aClassNames as $sClassName)
        {
            $xCallableObject = $this->xRepository->getCallableObject($sClassName);
            $sCode .= $this->getCallableScript($xCallableObject, $sClassName, $aProtectedMethods);
        }

        return $sCode;
    }

    /**
     * @inheritDoc
     */
    public function canProcessRequest(): bool
    {
        // Check the validity of the class name
        if(($this->sRequestedClass !== null && !$this->xValidator->validateClass($this->sRequestedClass)) ||
            ($this->sRequestedMethod !== null && !$this->xValidator->validateMethod($this->sRequestedMethod)))
        {
            $this->sRequestedClass = null;
            $this->sRequestedMethod = null;
        }
        return ($this->sRequestedClass !== null && $this->sRequestedMethod !== null);
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function processRequest(): bool
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
            throw new SetupException($this->xTranslator->trans('errors.objects.invalid',
                ['class' => $this->sRequestedClass, 'method' => $this->sRequestedMethod]));
        }

        // Call the requested method
        $aArgs = $this->xRequestHandler->processArguments();
        $xResponse = $xCallableObject->call($this->sRequestedMethod, $aArgs);
        if(($xResponse))
        {
            $this->xResponseManager->append($xResponse);
        }
        return true;
    }
}
