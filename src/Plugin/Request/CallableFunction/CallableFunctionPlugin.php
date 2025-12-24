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
use Jaxon\Di\Container;
use Jaxon\App\I18n\Translator;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AbstractRequestPlugin;
use Jaxon\Plugin\JsCode;
use Jaxon\Plugin\JsCodeGeneratorInterface;
use Jaxon\Request\Target;
use Jaxon\Request\Validator;
use Jaxon\Utils\Template\TemplateEngine;
use Psr\Http\Message\ServerRequestInterface;
use Exception;

use function array_keys;
use function count;
use function implode;
use function is_array;
use function is_string;
use function md5;
use function trim;

class CallableFunctionPlugin extends AbstractRequestPlugin implements JsCodeGeneratorInterface
{
    /**
     * The registered functions names
     *
     * @var array
     */
    protected $aFunctions = [];

    /**
     * The registered functions options
     *
     * @var array
     */
    protected $aOptions = [];

    /**
     * The constructor
     *
     * @param string $sPrefix
     * @param bool $bDebug
     * @param Container $di
     * @param TemplateEngine $xTemplateEngine
     * @param Translator $xTranslator
     * @param Validator $xValidator
     */
    public function __construct(private string $sPrefix, private bool $bDebug,
        private Container $di, private TemplateEngine $xTemplateEngine,
        private Translator $xTranslator, private Validator $xValidator)
    {}

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
    public function getCallable(string $sCallable): CallableFunction|null
    {
        $sFunction = trim($sCallable);
        if(!isset($this->aFunctions[$sFunction]))
        {
            return null;
        }
        $xCallable = new CallableFunction($this->di, $sFunction,
            $this->sPrefix . $sFunction, $this->aFunctions[$sFunction]);
        foreach($this->aOptions[$sFunction] as $sName => $sValue)
        {
            $xCallable->configure($sName, $sValue);
        }
        return $xCallable;
    }

    /**
     * Generate the javascript function stub that is sent to the browser on initial page load
     *
     * @param CallableFunction $xFunction
     *
     * @return string
     */
    private function getCallableScript(CallableFunction $xFunction): string
    {
        $aOptions = [];
        foreach($xFunction->getOptions() as $sKey => $sValue)
        {
            $aOptions[] = "$sKey: $sValue";
        }

        return $this->xTemplateEngine->render('jaxon::callables/function.js', [
            'sName' => $xFunction->getName(),
            'sJsName' => $xFunction->getJsName(),
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
            $xFunction = $this->getCallable($sFunction);
            $aScripts[] = trim($this->getCallableScript($xFunction));
        }
        return new JsCode(implode("\n", $aScripts) . "\n");
    }

    /**
     * @inheritDoc
     */
    public static function canProcessRequest(ServerRequestInterface $xRequest): bool
    {
        $aCall = $xRequest->getAttribute('jxncall');
        // throw new \Exception(json_encode(['call' => $aCall]));
        return $aCall !== null && ($aCall['type'] ?? '') === 'func' && isset($aCall['name']);
    }

    /**
     * @inheritDoc
     */
    public function setTarget(ServerRequestInterface $xRequest): Target
    {
        $aCall = $xRequest->getAttribute('jxncall');
        $this->xTarget = Target::makeFunction(trim($aCall['name']));
        return $this->xTarget;
    }

    /**
     * @param Exception $xException
     * @param string $sErrorMessage
     *
     * @throws RequestException
     * @return never
     */
    private function throwException(Exception $xException, string $sErrorMessage): void
    {
        $this->di->getLogger()->error($xException->getMessage());
        throw new RequestException($sErrorMessage . (!$this->bDebug ? '' :
            "\n" . $xException->getMessage()));
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function processRequest(): void
    {
        $sRequestedFunction = $this->xTarget->getFunctionName();

        // Security check: make sure the requested function was registered.
        if(!$this->xValidator->validateFunction($sRequestedFunction) ||
            !isset($this->aFunctions[$sRequestedFunction]))
        {
            // Unable to find the requested function
            throw new RequestException($this->xTranslator->trans('errors.functions.invalid',
                ['name' => $sRequestedFunction]));
        }

        try
        {
            /** @var CallableFunction */
            $xFunction = $this->getCallable($sRequestedFunction);
        }
        catch(Exception $e)
        {
            // Unable to find the requested function
            $this->throwException($e, $this->xTranslator->trans('errors.functions.invalid',
                ['name' => $sRequestedFunction]));
        }
        try
        {
            $xFunction->call($this->xTarget->args());
        }
        catch(Exception $e)
        {
            // Unable to execute the requested function
            $this->throwException($e, $this->xTranslator->trans('errors.functions.call',
                ['name' => $sRequestedFunction]));
        }
    }
}
