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
use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AbstractRequestPlugin;
use Jaxon\Request\Target;
use Jaxon\Request\Validator;
use Jaxon\Utils\Template\TemplateEngine;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

use function array_reduce;
use function is_array;
use function is_string;
use function md5;
use function str_replace;
use function strpos;
use function strlen;
use function substr;
use function trim;
use function uksort;

class CallableClassPlugin extends AbstractRequestPlugin
{
    /**
     * The class constructor
     *
     * @param string $sPrefix
     * @param bool $bDebug
     * @param Container $di
     * @param ComponentContainer $cdi
     * @param ComponentRegistry $xRegistry
     * @param TemplateEngine $xTemplateEngine
     * @param Translator $xTranslator
     * @param Validator $xValidator
     */
    public function __construct(private string $sPrefix, private bool $bDebug,
        private Container $di, private ComponentContainer $cdi,
        private ComponentRegistry $xRegistry, private TemplateEngine $xTemplateEngine,
        private Translator $xTranslator, private Validator $xValidator)
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
     * Generate client side javascript code for namespaces
     *
     * @return string
     */
    private function getNamespacesScript(): string
    {
        $sCode = '';
        $aJsClasses = [];
        foreach($this->xRegistry->getNamespaces() as $sNamespace)
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
     * @param CallableObject $xCallableObject The corresponding callable object
     *
     * @return string
     */
    private function getCallableScript(CallableObject $xCallableObject): string
    {
        if($xCallableObject->excluded())
        {
            return '';
        }

        return $this->xTemplateEngine->render('jaxon::callables/object.js', [
            'sPrefix' => $this->sPrefix,
            'sClass' => $xCallableObject->getJsName(),
            'aMethods' => $xCallableObject->getCallableMethods(),
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
        $this->xRegistry->registerAllComponents();
        $aCallableObjects = $this->cdi->getCallableObjects();
        // Sort the options by key length asc
        uksort($aCallableObjects, function($name1, $name2) {
            return strlen($name1) - strlen($name2);
        });

        return array_reduce($aCallableObjects, function($sCode, $xCallableObject) {
            return $sCode . $this->getCallableScript($xCallableObject);
        }, $this->getNamespacesScript());
    }

    /**
     * @inheritDoc
     */
    public static function canProcessRequest(ServerRequestInterface $xRequest): bool
    {
        $aCall = $xRequest->getAttribute('jxncall');
        return $aCall !== null && ($aCall['type'] ?? '') === 'class' &&
            isset($aCall['name']) && isset($aCall['method']);
    }

    /**
     * @inheritDoc
     */
    public function setTarget(ServerRequestInterface $xRequest): Target
    {
        $aCall = $xRequest->getAttribute('jxncall');
        $this->xTarget = Target::makeClass(trim($aCall['name']), trim($aCall['method']));
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
        $this->di->getLogger()->error($sMessage);
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

            $sError = 'errors.objects.call';
            if(!$xCallableObject->excluded($sMethodName))
            {
                $xCallableObject->call($this->xTarget);
                return;
            }

            // Unable to find the requested class or method
            $this->throwException('', 'errors.objects.excluded', $aErrorParams);
        }
        catch(ReflectionException|SetupException $e)
        {
            // Unable to execute the requested class or method
            $this->throwException($e->getMessage(), $sError, $aErrorParams);
        }
    }
}
