<?php

/**
 * CallableClassPlugin.php - Jaxon callable class plugin
 *
 * This class registers user defined callable classes, and calls their methods on user request.
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
use Jaxon\App\I18n\Translator;
use Jaxon\Di\ComponentContainer;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AbstractRequestPlugin;
use Jaxon\Plugin\JsCode;
use Jaxon\Plugin\JsCodeGeneratorInterface;
use Jaxon\Request\Validator;
use Jaxon\Utils\Template\TemplateEngine;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;

use function array_map;
use function count;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function md5;
use function str_repeat;
use function trim;

class CallableClassPlugin extends AbstractRequestPlugin implements JsCodeGeneratorInterface
{
    /**
     * @var CallableObject|null
     */
    protected CallableObject|null $xCallableAction = null;

    /**
     * @var array<CallableObjectProxy>
     */
    private array $aCallableObjects = [];

    /**
     * @var array<string>
     */
    private array $aCallableParams = [];

    /**
     * @param string $sPrefix
     * @param bool $bDebug
     * @param ComponentContainer $cdi
     * @param LoggerInterface $xLogger
     * @param ComponentRegistry $xRegistry
     * @param Translator $xTranslator
     * @param TemplateEngine $xTemplateEngine
     * @param Validator $xValidator
     */
    public function __construct(private string $sPrefix, bool $bDebug,
        private ComponentContainer $cdi, LoggerInterface $xLogger,
        private ComponentRegistry $xRegistry, private Translator $xTranslator,
        private TemplateEngine $xTemplateEngine, private Validator $xValidator)
    {
        $this->bDebug = $bDebug;
        $this->xLogger = $xLogger;
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
        $this->xRegistry->registerComponent($sClassName, $aOptions);
        return true;
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function makeCallableProxy(string $sCallable): CallableObjectProxy|null
    {
        return $this->cdi->makeCallableObject($sCallable);
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        $this->xRegistry->registerAllComponents();
        return md5($this->xRegistry->getHash());
    }

    /**
     * Add a callable object to the script generator
     *
     * @param CallableObjectProxy $xCallableProxy
     *
     * @return void
     */
    private function addCallable(CallableObjectProxy $xCallableProxy): void
    {
        $aCallableMethods = $xCallableProxy->getCallableMethods();
        if($xCallableProxy->excluded() || count($aCallableMethods) === 0)
        {
            return;
        }

        $aCallableObject = &$this->aCallableObjects;
        $sJsName = $xCallableProxy->getJsName();
        foreach(explode('.', $sJsName) as $sName)
        {
            if(!isset($aCallableObject['children'][$sName]))
            {
                $aCallableObject['children'][$sName] = [];
            }
            $aCallableObject = &$aCallableObject['children'][$sName];
        }

        $sJsParam = $xCallableProxy->getJsParam();

        $aCallableObject['methods'] = $aCallableMethods;
        $aCallableObject['param'] = $sJsParam;

        // Add the js param to the list, if it is not already in.
        if(isset($this->aCallableParams[$sJsParam]))
        {
            $aCallableObject['index'] = $this->aCallableParams[$sJsParam];
            return;
        }

        $nIndex = count($this->aCallableParams);
        $this->aCallableParams[$sJsParam] = $nIndex;
        $aCallableObject['index'] = $nIndex;
    }

    /**
     * @param string $sIndent
     * @param array $aTemplateVars
     *
     * @return string
     */
    private function renderMethod(string $sIndent, array $aTemplateVars): string
    {
        $aOptions = [];
        foreach($aTemplateVars['aMethod']['options'] as $sKey => $sValue)
        {
            $aOptions[] = "$sKey: $sValue";
        }
        $aTemplateVars['sArguments'] = count($aOptions) === 0 ? 'args' :
            'args, { ' . implode(', ', $aOptions) . ' }';

        return $sIndent . trim($this->xTemplateEngine
            ->render('jaxon::callables/method.js', $aTemplateVars));
    }

    /**
     * @param string $sJsClass
     * @param array $aCallable
     * @param int $nIndent
     *
     * @return string
     */
    private function renderCallable(string $sJsClass, array $aCallable, int $nIndent): string
    {
        $nIndent += 2; // Indentation.
        $sIndent = str_repeat(' ', $nIndent);

        $fMethodCallback = fn($aMethod) => $this->renderMethod($sIndent, [
            'aMethod' => $aMethod,
            'nIndex' => $aCallable['index'] ?? 0,
        ]);
        $aMethods = !isset($aCallable['methods']) ? [] :
            array_map($fMethodCallback, $aCallable['methods']);

        $aChildren = [];
        foreach($aCallable['children'] ?? [] as $sName => $aChild)
        {
            $aChildren[] = $this->renderChild("$sName:",
                "$sJsClass.$sName", $aChild, $nIndent) . ',';
        }

        return implode("\n", [...$aMethods, ...$aChildren]);
    }

    /**
     * @param string $sJsVar
     * @param string $sJsClass
     * @param array $aCallable
     * @param int $nIndent
     *
     * @return string
     */
    private function renderChild(string $sJsVar, string $sJsClass,
        array $aCallable, int $nIndent = 0): string
    {
        $sIndent = str_repeat(' ', $nIndent);
        $sScript = $this->renderCallable($sJsClass, $aCallable, $nIndent);

        return <<<CODE
$sIndent$sJsVar {
$sScript
$sIndent}
CODE;
    }

    /**
     * Generate client side javascript code for the registered callable objects
     *
     * @return string
     * @throws SetupException
     */
    public function getJsCode(): JsCode
    {
        $this->xRegistry->registerAllComponents();

        $this->aCallableParams = [];
        $this->aCallableObjects = ['children' => []];
        foreach($this->cdi->getCallableObjects() as $xCallableProxy)
        {
            $this->addCallable($xCallableProxy);
        }

        $aScripts = [
            $this->xTemplateEngine ->render('jaxon::callables/objects.js', [
                'aCallableParams' => $this->aCallableParams,
            ])
        ];
        foreach($this->aCallableObjects['children'] as $sJsClass => $aCallable)
        {
            $aScripts[] = $this->renderChild("{$this->sPrefix}$sJsClass =",
                $sJsClass, $aCallable) . ';';
        }
        return new JsCode(implode("\n", $aScripts) . "\n");
    }

    /**
     * @inheritDoc
     */
    public function getCallableAction(): CallableObject|null
    {
        return $this->xCallableAction;
    }

    /**
     * @inheritDoc
     */
    public function makeCallableAction(ServerRequestInterface $xRequest): CallableObject
    {
        $aCall = $xRequest->getAttribute('jxncall');
        $sClassName = trim($aCall['name']);
        $sMethodName = trim($aCall['method']);
        $aArgs = $aCall['args'] ?? [];
        $this->xCallableAction = new CallableObject($sClassName, $sMethodName, $aArgs);
        // Save the action in the DI container.
        $this->cdi->saveCallableAction($this->xCallableAction);
        return $this->xCallableAction;
    }

    /**
     * @inheritDoc
     */
    public static function canProcessRequest(ServerRequestInterface $xRequest): bool
    {
        $aCall = $xRequest->getAttribute('jxncall');
        return $aCall !== null && ($aCall['type'] ?? '') === 'class' &&
            isset($aCall['name']) && isset($aCall['method']) &&
            is_string($aCall['name']) && is_string($aCall['method']);
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function processRequest(): void
    {
        $sClassName = $this->xCallableAction->getClassName();
        $sMethodName = $this->xCallableAction->getMethodName();

        if(!$this->xValidator->validateJsObject($sClassName) ||
            !$this->xValidator->validateMethod($sMethodName))
        {
            $sMessage = 'Trying to call an invalid class or method.';
            $sError = 'errors.objects.invalid';
            $this->throwException($sMessage, $this->xTranslator->trans($sError, [
                'class' => $sClassName,
                'method' => $sMethodName,
            ]));
        }

        // Call the requested method
        try
        {
            $sError = 'errors.objects.find';
            $xCallableProxy = $this->makeCallableProxy($sClassName);
            if($xCallableProxy->excluded($sMethodName))
            {
                $sMessage = 'Trying to call an excluded method.';
                $sError = 'errors.objects.excluded';
                $this->throwException($sMessage, $this->xTranslator->trans($sError, [
                    'class' => $sClassName,
                    'method' => $sMethodName,
                ]));
            }

            $sError = 'errors.objects.call';
            $xCallableProxy->call($this->xCallableAction);
        }
        catch(ReflectionException|SetupException $e)
        {
            // Unable to execute the requested class or method
            $this->throwException($e->getMessage(), $this->xTranslator->trans($sError, [
                'class' => $sClassName,
                'method' => $sMethodName,
            ]));
        }
    }
}
