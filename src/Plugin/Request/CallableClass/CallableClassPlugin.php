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
use Jaxon\Request\Target;
use Jaxon\Request\Validator;
use Jaxon\Utils\Template\TemplateEngine;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;

use function array_map;
use function array_merge;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function md5;
use function str_repeat;
use function trim;

class CallableClassPlugin extends AbstractRequestPlugin
{
    /**
     * @var array
     */
    private array $aCallableObjects = [];

    /**
     * The class constructor
     *
     * @param string $sPrefix
     * @param LoggerInterface $xLogger
     * @param ComponentContainer $cdi
     * @param ComponentRegistry $xRegistry
     * @param Translator $xTranslator
     * @param TemplateEngine $xTemplateEngine
     * @param Validator $xValidator
     */
    public function __construct(private string $sPrefix,
        private LoggerInterface $xLogger, private ComponentContainer $cdi,
        private ComponentRegistry $xRegistry, private Translator $xTranslator,
        private TemplateEngine $xTemplateEngine, private Validator $xValidator)
    {}

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
    public function getCallable(string $sCallable): CallableObject|null
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
     * @param CallableObject $xCallableObject
     *
     * @return void
     */
    private function addCallable(CallableObject $xCallableObject): void
    {
        if($xCallableObject->excluded())
        {
            return;
        }

        $aCallableObject = &$this->aCallableObjects;
        foreach(explode('.', $xCallableObject->getJsName()) as $sName)
        {
            if(!isset($aCallableObject['children'][$sName]))
            {
                $aCallableObject['children'][$sName] = [];
            }
            $aCallableObject = &$aCallableObject['children'][$sName];
        }
        $aCallableObject['methods'] = $xCallableObject->getCallableMethods();
    }

    /**
     * @param string $sIndent
     * @param array $aTemplateVars
     *
     * @return string
     */
    private function renderMethod(string $sIndent, array $aTemplateVars): string
    {
        return $sIndent . trim($this->xTemplateEngine
            ->render('jaxon::callables/method.js', $aTemplateVars));
    }

    /**
     * @param string $sJsClass
     * @param array $aCallable
     * @param int $nRepeat
     *
     * @return string
     */
    private function renderCallable(string $sJsClass, array $aCallable, int $nRepeat): string
    {
        $nRepeat += 2; // Indentation.
        $sIndent = str_repeat(' ', $nRepeat);

        $fMethodCallback = fn($aMethod) => $this->renderMethod($sIndent,
            ['sJsClass' => $sJsClass, 'aMethod' => $aMethod]);
        $aMethods = !isset($aCallable['methods']) ? [] :
            array_map($fMethodCallback, $aCallable['methods']);

        $aChildren = [];
        foreach($aCallable['children'] ?? [] as $sName => $aChild)
        {
            $aChildren[] = $this->renderChild("$sName:", "$sJsClass.$sName",
                $aChild, $nRepeat) . ',';
        }

        return implode("\n", array_merge($aMethods, $aChildren));
    }

    /**
     * @param string $sJsVar
     * @param string $sJsClass
     * @param array $aCallable
     * @param int $nRepeat
     *
     * @return string
     */
    private function renderChild(string $sJsVar, string $sJsClass,
        array $aCallable, int $nRepeat = 0): string
    {
        $sIndent = str_repeat(' ', $nRepeat);
        $sScript = $this->renderCallable($sJsClass, $aCallable, $nRepeat);

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
    public function getScript(): string
    {
        $this->xRegistry->registerAllComponents();

        $this->aCallableObjects = ['children' => []];
        foreach($this->cdi->getCallableObjects() as $xCallableObject)
        {
            $this->addCallable($xCallableObject);
        }

        $aScripts = [];
        foreach($this->aCallableObjects['children'] as $sJsClass => $aCallable)
        {
            $aScripts[] = $this->renderChild("{$this->sPrefix}$sJsClass =",
                $sJsClass, $aCallable) . ';';
        }
        return implode("\n", $aScripts) . "\n";
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
     */
    public function setTarget(ServerRequestInterface $xRequest): Target
    {
        $this->xTarget = Target::makeClass($xRequest->getAttribute('jxncall'));
        return $this->xTarget;
    }

    /**
     * @param string $sExceptionMessage
     * @param string $sErrorCode
     * @param array $aErrorParams
     *
     * @throws RequestException
     * @return void
     */
    private function throwException(string $sExceptionMessage,
        string $sErrorCode, array $aErrorParams = []): void
    {
        $sMessage = $this->xTranslator->trans($sErrorCode, $aErrorParams) .
            (!$sExceptionMessage ? '' : "\n$sExceptionMessage");
        $this->xLogger->error($sMessage);
        throw new RequestException($sMessage);
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function processRequest(): void
    {
        $sClassName = $this->xTarget->getClassName();
        $sMethodName = $this->xTarget->getMethodName();
        // Will be used to print a translated error message.
        $aErrorParams = ['class' => $sClassName, 'method' => $sMethodName];

        if(!$this->xValidator->validateJsObject($sClassName) ||
            !$this->xValidator->validateMethod($sMethodName))
        {
            // Unable to find the requested object or method
            $this->throwException('', 'errors.objects.invalid', $aErrorParams);
        }

        // Call the requested method
        try
        {
            $sError = 'errors.objects.find';
            /** @var CallableObject */
            $xCallableObject = $this->getCallable($sClassName);

            if($xCallableObject->excluded($sMethodName))
            {
                // Unable to find the requested class or method
                $this->throwException('', 'errors.objects.excluded', $aErrorParams);
            }

            $sError = 'errors.objects.call';
            $xCallableObject->call($this->xTarget);
        }
        catch(ReflectionException|SetupException $e)
        {
            // Unable to execute the requested class or method
            $this->throwException($e->getMessage(), $sError, $aErrorParams);
        }
    }
}
