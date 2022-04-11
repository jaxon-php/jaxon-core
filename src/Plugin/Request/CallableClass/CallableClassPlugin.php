<?php

/**
 * CallableClassPlugin.php - Jaxon callable class plugin
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

namespace Jaxon\Plugin\Request\CallableClass;

use Jaxon\Jaxon;
use Jaxon\CallableClass;
use Jaxon\App\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\RequestPlugin;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Request\Target;
use Jaxon\Request\Validator;
use Jaxon\Response\ResponseInterface;
use Jaxon\Utils\Template\TemplateEngine;
use Psr\Http\Message\ServerRequestInterface;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

use function is_array;
use function is_string;
use function is_subclass_of;
use function md5;
use function strlen;
use function trim;
use function uksort;

class CallableClassPlugin extends RequestPlugin
{
    /**
     * @var string
     */
    protected $sPrefix;

    /**
     * @var Container
     */
    protected $di;

    /**
     * The parameter reader
     *
     * @var ParameterReader
     */
    protected $xParameterReader;

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
     * The methods that must not be exported to js
     *
     * @var array
     */
    protected $aProtectedMethods = [];

    /**
     * The class constructor
     *
     * @param string  $sPrefix
     * @param Container $di
     * @param ParameterReader $xParameterReader
     * @param CallableRegistry $xRegistry    The callable class registry
     * @param CallableRepository $xRepository    The callable object repository
     * @param TemplateEngine $xTemplateEngine
     * @param Translator $xTranslator
     * @param Validator $xValidator
     */
    public function __construct(string $sPrefix, Container $di, ParameterReader $xParameterReader,
        CallableRegistry $xRegistry, CallableRepository $xRepository,
        TemplateEngine $xTemplateEngine, Translator $xTranslator, Validator $xValidator)
    {
        $this->di = $di;
        $this->sPrefix = $sPrefix;
        $this->xParameterReader = $xParameterReader;
        $this->xRegistry = $xRegistry;
        $this->xRepository = $xRepository;
        $this->xTemplateEngine = $xTemplateEngine;
        $this->xTranslator = $xTranslator;
        $this->xValidator = $xValidator;

        // The methods of the CallableClass class must not be exported
        $xCallableClass = new ReflectionClass(CallableClass::class);
        foreach($xCallableClass->getMethods(ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $this->aProtectedMethods[] = $xMethod->getName();
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
     * @throws SetupException
     */
    public function checkOptions(string $sCallable, $xOptions): array
    {
        if(!$this->xValidator->validateClass(trim($sCallable)))
        {
            throw new SetupException($this->xTranslator->trans('errors.objects.invalid-declaration'));
        }
        if(is_string($xOptions))
        {
            $xOptions = ['include' => $xOptions];
        }
        elseif(!is_array($xOptions))
        {
            throw new SetupException($this->xTranslator->trans('errors.objects.invalid-declaration'));
        }
        return $xOptions;
    }

    /**
     * @inheritDoc
     */
    public function register(string $sType, string $sCallable, array $aOptions): bool
    {
        $sClassName = trim($sCallable);
        $this->xRepository->addClass($sClassName, $aOptions);
        return true;
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function getCallable(string $sCallable)
    {
        return $this->xRegistry->getCallableObject($sCallable);
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        $this->xRegistry->parseCallableClasses();
        return md5($this->xRepository->getHash());
    }

    /**
     * Generate client side javascript code for namespaces
     *
     * @return string
     */
    private function getNamespacesScript(): string
    {
        $sCode = '';
        $aJsClasses = [];
        $aNamespaces = $this->xRepository->getNamespaces();
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
                    $sCode .= $this->sPrefix . "$sJsClass = {};\n";
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
     * @param string $sClassName
     * @param CallableObject $xCallableObject The corresponding callable object
     *
     * @return string
     */
    private function getCallableScript(string $sClassName, CallableObject $xCallableObject): string
    {
        $aProtectedMethods = is_subclass_of($sClassName, CallableClass::class) ? $this->aProtectedMethods : [];
        return $this->xTemplateEngine->render('jaxon::callables/object.js', [
            'sPrefix' => $this->sPrefix,
            'sClass' => $xCallableObject->getJsName(),
            'aMethods' => $xCallableObject->getMethods($aProtectedMethods),
        ]);
    }

    /**
     * Generate client side javascript code for the registered callable objects
     *
     * @return string
     * @throws SetupException
     */
    public function getScript(): string
    {
        $this->xRegistry->parseCallableClasses();
        $aCallableObjects = $this->xRepository->getCallableObjects();
        // Sort the options by key length asc
        uksort($aCallableObjects, function($name1, $name2) {
            return strlen($name1) - strlen($name2);
        });

        $sCode = $this->getNamespacesScript();
        foreach($aCallableObjects as $sClassName => $xCallableObject)
        {
            $sCode .= $this->getCallableScript($sClassName, $xCallableObject);
        }
        return $sCode;
    }

    /**
     * @inheritDoc
     */
    public static function canProcessRequest(ServerRequestInterface $xRequest): bool
    {
        $aBody = $xRequest->getParsedBody();
        if(is_array($aBody))
        {
            return isset($aBody['jxncls']) && isset($aBody['jxnmthd']);
        }
        $aParams = $xRequest->getQueryParams();
        return isset($aParams['jxncls']) && isset($aParams['jxnmthd']);
    }

    /**
     * @inheritDoc
     */
    public function setTarget(ServerRequestInterface $xRequest)
    {
        $aBody = $xRequest->getParsedBody();
        if(is_array($aBody))
        {
            $this->xTarget = Target::makeClass(trim($aBody['jxncls']), trim($aBody['jxnmthd']));
            return;
        }
        $aParams = $xRequest->getQueryParams();
        $this->xTarget = Target::makeClass(trim($aParams['jxncls']), trim($aParams['jxnmthd']));
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function processRequest(): ?ResponseInterface
    {
        $sRequestedClass = $this->xTarget->getClassName();
        $sRequestedMethod = $this->xTarget->getMethodName();

        if(!$this->xValidator->validateClass($sRequestedClass) ||
            !$this->xValidator->validateMethod($sRequestedMethod))
        {
            // Unable to find the requested object or method
            throw new RequestException($this->xTranslator->trans('errors.objects.invalid',
                ['class' => $sRequestedClass, 'method' => $sRequestedMethod]));
        }

        // Call the requested method
        try
        {
            $xCallableObject = $this->xRegistry->getCallableObject($sRequestedClass);
            return $xCallableObject->call($sRequestedMethod, $this->xParameterReader->args());
        }
        catch(ReflectionException $e)
        {
            // Unable to find the requested class or method
            $this->di->getLogger()->error($e->getMessage());
            throw new RequestException($this->xTranslator->trans('errors.objects.invalid',
                ['class' => $sRequestedClass, 'method' => $sRequestedMethod]));
        }
        catch(SetupException $e)
        {
            // Unable to get the callable object
            throw new RequestException($this->xTranslator->trans('errors.objects.invalid',
                ['class' => $sRequestedClass, 'method' => $sRequestedMethod]));
        }
    }
}
