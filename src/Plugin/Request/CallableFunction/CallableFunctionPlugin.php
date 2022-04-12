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
use Jaxon\App\I18n\Translator;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\RequestPlugin;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Request\Target;
use Jaxon\Request\Validator;
use Jaxon\Response\ResponseInterface;
use Jaxon\Utils\Template\TemplateEngine;
use Psr\Http\Message\ServerRequestInterface;

use function array_keys;
use function implode;
use function is_array;
use function is_string;
use function md5;
use function trim;

class CallableFunctionPlugin extends RequestPlugin
{
    /**
     * @var string
     */
    private $sPrefix;

    /**
     * The parameter reader
     *
     * @var ParameterReader
     */
    protected $xParameterReader;

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
     * @param ParameterReader $xParameterReader
     * @param TemplateEngine $xTemplateEngine
     * @param Translator $xTranslator
     * @param Validator $xValidator
     */
    public function __construct(string $sPrefix, ParameterReader $xParameterReader,
        TemplateEngine $xTemplateEngine, Translator $xTranslator, Validator $xValidator)
    {
        $this->sPrefix = $sPrefix;
        $this->xParameterReader = $xParameterReader;
        $this->xTemplateEngine = $xTemplateEngine;
        $this->xTranslator = $xTranslator;
        $this->xValidator = $xValidator;
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
    public function getCallable(string $sCallable)
    {
        $sFunction = trim($sCallable);
        if(!isset($this->aFunctions[$sFunction]))
        {
            return null;
        }
        $xCallable = new CallableFunction($sFunction, $this->sPrefix . $sFunction, $this->aFunctions[$sFunction]);
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
        return $this->xTemplateEngine->render('jaxon::callables/function.js', [
            'sName' => $xFunction->getName(),
            'sJsName' => $xFunction->getJsName(),
            'aOptions' => $xFunction->getOptions(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getScript(): string
    {
        $code = '';
        foreach(array_keys($this->aFunctions) as $sFunction)
        {
            $xFunction = $this->getCallable($sFunction);
            $code .= $this->getCallableScript($xFunction);
        }
        return $code;
    }

    /**
     * @inheritDoc
     */
    public static function canProcessRequest(ServerRequestInterface $xRequest): bool
    {
        $aBody = $xRequest->getParsedBody();
        if(is_array($aBody))
        {
            return isset($aBody['jxnfun']);
        }
        $aParams = $xRequest->getQueryParams();
        return isset($aParams['jxnfun']);
    }

    /**
     * @inheritDoc
     */
    public function setTarget(ServerRequestInterface $xRequest)
    {
        $aBody = $xRequest->getParsedBody();
        if(is_array($aBody))
        {
            $this->xTarget = Target::makeFunction(trim($aBody['jxnfun']));
            return;
        }
        $aParams = $xRequest->getQueryParams();
        $this->xTarget = Target::makeFunction(trim($aParams['jxnfun']));
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function processRequest(): ?ResponseInterface
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

        $xFunction = $this->getCallable($sRequestedFunction);
        return $xFunction->call($this->xParameterReader->args());
    }
}
