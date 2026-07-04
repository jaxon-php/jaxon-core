<?php

/**
 * CallableFunctionPlugin.php - Jaxon user function plugin
 *
 * This class registers user defined functions, generates client side javascript code,
 * and calls them on user request
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

namespace Jaxon\Plugin\Request\CallableFunction;

use Jaxon\Jaxon;
use Jaxon\Di\ComponentContainer;
use Jaxon\App\I18n\Translator;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AbstractRequestPlugin;
use Jaxon\Plugin\JsCode;
use Jaxon\Plugin\JsCodeGeneratorInterface;
use Jaxon\Request\Validator;
use Jaxon\Utils\Template\TemplateEngine;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Exception;

use function array_keys;
use function array_map;
use function array_values;
use function count;
use function implode;
use function is_array;
use function is_string;
use function md5;
use function trim;

class CallableFunctionPlugin extends AbstractRequestPlugin implements JsCodeGeneratorInterface
{
    /**
     * @var CallableFunction|null
     */
    protected CallableFunction|null $xCallableAction = null;

    /**
     * The registered functions names
     *
     * @var array
     */
    protected array $aFunctions = [];

    /**
     * The registered functions options
     *
     * @var array
     */
    protected array $aOptions = [];

    /**
     * @param string $sPrefix
     * @param bool $bDebug
     * @param ComponentContainer $cdi
     * @param LoggerInterface $xLogger
     * @param Translator $xTranslator
     * @param Validator $xValidator
     * @param TemplateEngine $xTemplateEngine
     */
    public function __construct(private string $sPrefix, bool $bDebug,
        private ComponentContainer $cdi, LoggerInterface $xLogger,
        private Translator $xTranslator, private Validator $xValidator,
        private TemplateEngine $xTemplateEngine)
    {
        $this->bDebug = $bDebug;
        $this->xLogger = $xLogger;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return Jaxon::CALLABLE_FUNCTION;
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function checkOptions(string $sCallable, $xOptions): array
    {
        if(!$this->xValidator->validateFunction(trim($sCallable)))
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
     * Register a user defined function
     *
     * @param string $sType    The type of request handler being registered
     * @param string $sCallable    The name of the function being registered
     * @param array $aOptions    The associated options
     *
     * @return bool
     */
    public function register(string $sType, string $sCallable, array $aOptions): bool
    {
        $sPhpFunction = trim($sCallable);
        $sFunction = $sPhpFunction;
        // Check if an alias is defined
        if(isset($aOptions['alias']))
        {
            $sFunction = (string)$aOptions['alias'];
            unset($aOptions['alias']);
        }
        $this->aFunctions[$sFunction] = $sPhpFunction;
        $this->aOptions[$sFunction] = $aOptions;
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        return md5(implode('', array_keys($this->aFunctions)));
    }

    /**
     * @inheritDoc
     */
    public function makeCallableProxy(string $sCallable): CallableFunctionProxy|null
    {
        $sFunction = trim($sCallable);
        if(!isset($this->aFunctions[$sFunction]))
        {
            return null;
        }
        $xCallable = new CallableFunctionProxy($this->cdi, $sFunction,
            "{$this->sPrefix}$sFunction", $this->aFunctions[$sFunction]);
        foreach($this->aOptions[$sFunction] as $sName => $sValue)
        {
            $xCallable->configure($sName, $sValue);
        }
        return $xCallable;
    }

    /**
     * Generate the javascript function stub that is sent to the browser on initial page load
     *
     * @param CallableFunctionProxy $xCallableProxy
     *
     * @return string
     */
    private function getCallableScript(CallableFunctionProxy $xCallableProxy): string
    {
        $aOptions = $xCallableProxy->getOptions();
        $aOptions = array_map(fn($sKey, $sValue) => "$sKey: $sValue",
            array_keys($aOptions), array_values($aOptions));
        return $this->xTemplateEngine->render('jaxon::callables/function.js', [
            'sName' => $xCallableProxy->getName(),
            'sJsName' => $xCallableProxy->getJsName(),
            'sArguments' => count($aOptions) === 0 ? 'args' :
                'args, { ' . implode(',', $aOptions) . ' }',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getJsCode(): JsCode
    {
        $aScripts = [];
        foreach(array_keys($this->aFunctions) as $sFunction)
        {
            $xCallableProxy = $this->makeCallableProxy($sFunction);
            $aScripts[] = trim($this->getCallableScript($xCallableProxy));
        }
        return new JsCode(implode("\n", $aScripts) . "\n");
    }

    /**
     * @inheritDoc
     */
    public function getCallableAction(): CallableFunction|null
    {
        return $this->xCallableAction;
    }

    /**
     * @inheritDoc
     */
    public function makeCallableAction(ServerRequestInterface $xRequest): CallableFunction
    {
        $aCall = $xRequest->getAttribute('jxncall');
        $sFunctionName = trim($aCall['name']);
        $aArgs = $aCall['args'] ?? [];
        $this->xCallableAction = new CallableFunction($sFunctionName, $aArgs);
        return $this->xCallableAction;
    }

    /**
     * @inheritDoc
     */
    public static function canProcessRequest(ServerRequestInterface $xRequest): bool
    {
        $aCall = $xRequest->getAttribute('jxncall');
        // throw new \Exception(json_encode(['call' => $aCall]));
        return $aCall !== null && ($aCall['type'] ?? '') === 'func' &&
            isset($aCall['name']) && is_string($aCall['name']);
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function processRequest(): void
    {
        $sRequestedFunction = $this->xCallableAction->func();

        // Security check: make sure the requested function was registered.
        $bIsValid = $this->xValidator->validateFunction($sRequestedFunction);
        if(!$bIsValid || !isset($this->aFunctions[$sRequestedFunction]))
        {
            $sMessage = 'Trying to call an invalid or unregistered function.';
            $sError = 'errors.functions.invalid';
            $this->throwException($sMessage, $this->xTranslator->trans($sError, [
                'name' => $sRequestedFunction,
            ]));
        }

        try
        {
            $sError = 'errors.functions.invalid'; // Unable to find the requested function.
            $xCallableProxy = $this->makeCallableProxy($sRequestedFunction);

            $sError = 'errors.functions.call';
            $xCallableProxy->call($this->xCallableAction);
        }
        catch(Exception $e)
        {
            // Unable to execute the requested function
            $this->throwException($e->getMessage(), $this->xTranslator->trans($sError, [
                'name' => $sRequestedFunction,
            ]));
        }
    }
}
